<?php

namespace App\Services;

use App\Models\DetallePedido;
use App\Models\HistorialEstadoPedido;
use App\Models\HistorialRespuestaProducto;
use App\Models\Inventario;
use App\Models\Notificacion;
use App\Models\PedidoVendedor;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LogicException;

class RespuestaVendedorService
{
    /**
     * Registrar la respuesta del vendedor para un producto solicitado.
     */
    public function responderProducto(
        Usuario $usuario,
        int $idDetallePedido,
        array $respuesta
    ): DetallePedido {
        $datos = Validator::make($respuesta, [
            'estado_respuesta' => [
                'required',
                'in:aceptado,rechazado',
            ],
            'disponibilidad_producto' => [
                'required',
                'boolean',
            ],
            'motivo_respuesta' => [
                'nullable',
                'string',
            ],
            'dar_de_baja_publicacion' => [
                'nullable',
                'boolean',
            ],
            'justificacion_publicacion' => [
                'nullable',
                'string',
            ],
        ])->validate();

        $disponible = (bool) $datos['disponibilidad_producto'];
        $aceptado = $datos['estado_respuesta'] === 'aceptado';

        if (! $disponible && $aceptado) {
            throw ValidationException::withMessages([
                'estado_respuesta' => 'No se puede aceptar un producto que no está disponible.',
            ]);
        }

        if (! $aceptado && empty($datos['motivo_respuesta'])) {
            throw ValidationException::withMessages([
                'motivo_respuesta' => 'Debes indicar el motivo del rechazo.',
            ]);
        }

        if (! $disponible) {
            if (! array_key_exists('dar_de_baja_publicacion', $datos)
                || $datos['dar_de_baja_publicacion'] === null) {
                throw ValidationException::withMessages([
                    'dar_de_baja_publicacion' => 'Debes indicar si deseas dar de baja la publicación.',
                ]);
            }

            if (
                ! $datos['dar_de_baja_publicacion']
                && empty($datos['justificacion_publicacion'])
            ) {
                throw ValidationException::withMessages([
                    'justificacion_publicacion' => 'Debes justificar por qué mantienes publicada esta publicación.',
                ]);
            }
        }

        return DB::transaction(function () use (
            $usuario,
            $idDetallePedido,
            $datos,
            $disponible,
            $aceptado
        ) {
            $detalleInicial = DetallePedido::query()
                ->findOrFail($idDetallePedido);

            $solicitud = PedidoVendedor::query()
                ->whereKey($detalleInicial->id_pedido_vendedor)
                ->lockForUpdate()
                ->firstOrFail();

            $detalle = DetallePedido::query()
                ->whereKey($idDetallePedido)
                ->lockForUpdate()
                ->firstOrFail();

            $vendedor = $solicitud->vendedor()
                ->lockForUpdate()
                ->firstOrFail();

            $usuarioVendedor = Usuario::query()
                ->whereKey($vendedor->id_usuario)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $usuarioVendedor->id_usuario
                !== (int) $usuario->id_usuario
            ) {
                throw new AuthorizationException(
                    'No tienes autorización para responder esta solicitud.'
                );
            }

            if (! $usuarioVendedor->activo || ! $usuario->activo) {
                throw new AuthorizationException(
                    'La cuenta del vendedor debe estar activa.'
                );
            }

            if ($vendedor->estado !== 'activo') {
                throw new AuthorizationException(
                    'El perfil de vendedor debe estar activo.'
                );
            }

            if ($detalle->estado_respuesta !== 'pendiente') {
                throw new LogicException(
                    'Este producto ya tiene una respuesta registrada.'
                );
            }

            $producto = Producto::query()
                ->whereKey($detalle->id_producto)
                ->lockForUpdate()
                ->firstOrFail();

            $inventario = Inventario::query()
                ->where('id_producto', $producto->id_producto)
                ->lockForUpdate()
                ->firstOrFail();

            $cantidad = (int) $detalle->cantidad;

            if (
                $inventario->stock_fisico < 0
                || $inventario->stock_reservado < $cantidad
                || $inventario->stock_reservado > $inventario->stock_fisico
            ) {
                throw ValidationException::withMessages([
                    'inventario' => 'El inventario presenta inconsistencias. No se puede procesar la respuesta.',
                ]);
            }

            if ($aceptado && $inventario->stock_fisico < $cantidad) {
                throw ValidationException::withMessages([
                    'inventario' => 'No existe suficiente stock físico para aceptar este producto.',
                ]);
            }

            $estadoAnterior = $detalle->estado_respuesta;

            // Aceptar: descontar stock físico y liberar la reserva.
            // Rechazar: liberar únicamente la reserva.
            if ($aceptado) {
                $inventario->stock_fisico -= $cantidad;
            }

            $inventario->stock_reservado -= $cantidad;
            $inventario->save();

            if (
                ! $disponible
                && $datos['dar_de_baja_publicacion']
            ) {
                $producto->activo = false;
                $producto->save();
            }

            $fechaRespuesta = now();

            $detalle->estado_respuesta = $datos['estado_respuesta'];
            $detalle->disponibilidad_producto = $disponible;
            $detalle->motivo_respuesta = $datos['motivo_respuesta'] ?? null;
            $detalle->dar_de_baja_publicacion =
                $datos['dar_de_baja_publicacion'] ?? null;
            $detalle->justificacion_publicacion =
                $datos['justificacion_publicacion'] ?? null;
            $detalle->fecha_respuesta = $fechaRespuesta;
            $detalle->save();

            HistorialRespuestaProducto::query()->create([
                'id_detalle_pedido' => $detalle->id_detalle_pedido,
                'id_usuario_responsable' => $usuarioVendedor->id_usuario,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $detalle->estado_respuesta,
                'disponibilidad_producto' => $disponible,
                'motivo_respuesta' => $detalle->motivo_respuesta,
                'dar_de_baja_publicacion' => $detalle->dar_de_baja_publicacion,
                'justificacion_publicacion' => $detalle->justificacion_publicacion,
                'fecha_respuesta' => $fechaRespuesta,
            ]);

            $pedido = $solicitud->pedido()->firstOrFail();

            $resultado = $aceptado ? 'aceptó' : 'rechazó';

            $mensaje = "El vendedor {$resultado} el producto "
                . "{$detalle->nombre_producto}.";

            if (! $aceptado) {
                $mensaje .= " Motivo: {$detalle->motivo_respuesta}";
            }

            Notificacion::query()->create([
                'id_usuario' => $pedido->id_comprador,
                'id_pedido_vendedor' => $solicitud->id_pedido_vendedor,
                'id_detalle_pedido' => $detalle->id_detalle_pedido,
                'tipo' => $aceptado
                    ? 'producto_aceptado'
                    : 'producto_rechazado',
                'mensaje' => $mensaje,
                'leida' => false,
            ]);

            $this->actualizarEstadoSolicitud(
                $solicitud,
                $usuarioVendedor
            );

            return $detalle->fresh([
                'producto',
                'pedidoVendedor',
                'historialRespuestas',
            ]);
        }, 3);
    }

    /**
     * Actualizar el estado agregado de la solicitud del vendedor.
     */
    private function actualizarEstadoSolicitud(
        PedidoVendedor $solicitud,
        Usuario $responsable
    ): void {
        $detalles = $solicitud->detalles()->get();

        $total = $detalles->count();

        $pendientes = $detalles
            ->where('estado_respuesta', 'pendiente')
            ->count();

        $aceptados = $detalles
            ->where('estado_respuesta', 'aceptado')
            ->count();

        $rechazados = $detalles
            ->where('estado_respuesta', 'rechazado')
            ->count();

        if ($pendientes === $total) {
            $nuevoEstado = 'pendiente';
        } elseif ($pendientes > 0) {
            $nuevoEstado = 'en_proceso';
        } elseif ($aceptados === $total) {
            $nuevoEstado = 'aceptado';
        } elseif ($rechazados === $total) {
            $nuevoEstado = 'rechazado';
        } else {
            $nuevoEstado = 'parcial';
        }

        $estadoAnterior = $solicitud->estado;

        if ($estadoAnterior === $nuevoEstado) {
            return;
        }

        $solicitud->estado = $nuevoEstado;
        $solicitud->fecha_respuesta = $pendientes === 0
            ? now()
            : null;

        $solicitud->save();

        HistorialEstadoPedido::query()->create([
            'id_pedido_vendedor' => $solicitud->id_pedido_vendedor,
            'estado' => $nuevoEstado,
            'comentario' => "El estado cambió de {$estadoAnterior} a {$nuevoEstado}.",
            'fecha_cambio' => now(),
            'id_usuario_responsable' => $responsable->id_usuario,
        ]);
    }
}
