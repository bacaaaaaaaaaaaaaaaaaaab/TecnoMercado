<?php

use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

test('se pueden listar todas las categorias', function () {
    Categoria::create(['nombre' => 'Electronica']);
    Categoria::create(['nombre' => 'Ropa']);

    $categorias = app(CategoriaService::class)->listar();

    expect($categorias)->toHaveCount(2);
});

test('se pueden listar solamente las categorias activas', function () {
    Categoria::create([
        'nombre' => 'Electronica',
        'activa' => true,
    ]);

    Categoria::create([
        'nombre' => 'Ropa',
        'activa' => false,
    ]);

    $categorias = app(CategoriaService::class)->listar(true);

    expect($categorias)->toHaveCount(1)
        ->and($categorias->first()->nombre)->toBe('Electronica');
});

test('se puede consultar una categoria existente', function () {
    $categoria = Categoria::create([
        'nombre' => 'Electronica',
    ]);

    $resultado = app(CategoriaService::class)
        ->obtener($categoria->id_categoria);

    expect($resultado->nombre)->toBe('Electronica');
});

test('no se puede consultar una categoria inexistente', function () {
    expect(fn () => app(CategoriaService::class)->obtener(999999))
        ->toThrow(ModelNotFoundException::class);
});

test('se puede crear una categoria', function () {
    $categoria = app(CategoriaService::class)->crear([
        'nombre' => 'Electronica',
        'descripcion' => 'Productos electronicos',
    ]);

    expect($categoria->nombre)->toBe('Electronica')
        ->and($categoria->descripcion)->toBe('Productos electronicos')
        ->and($categoria->activa)->toBeTrue();
});

test('no se permite crear categorias con nombres duplicados', function () {
    Categoria::create([
        'nombre' => 'Electronica',
    ]);

    expect(fn () => app(CategoriaService::class)->crear([
        'nombre' => 'Electronica',
    ]))->toThrow(ValidationException::class);
});

test('se puede actualizar una categoria conservando su nombre', function () {
    $categoria = Categoria::create([
        'nombre' => 'Electronica',
        'descripcion' => 'Descripcion original',
    ]);

    $actualizada = app(CategoriaService::class)->actualizar(
        $categoria->id_categoria,
        [
            'nombre' => 'Electronica',
            'descripcion' => 'Descripcion actualizada',
        ]
    );

    expect($actualizada->nombre)->toBe('Electronica')
        ->and($actualizada->descripcion)->toBe('Descripcion actualizada');
});

test('la actualizacion no permite modificar campos protegidos', function () {
    $categoria = Categoria::create([
        'nombre' => 'Electronica',
        'activa' => true,
    ]);

    $actualizada = app(CategoriaService::class)->actualizar(
        $categoria->id_categoria,
        [
            'nombre' => 'Electronica',
            'descripcion' => 'Actualizada',
            'id_categoria' => 999999,
            'activa' => false,
        ]
    );

    expect($actualizada->id_categoria)->toBe($categoria->id_categoria)
        ->and($actualizada->activa)->toBeTrue();
});

test('se puede desactivar una categoria sin eliminarla', function () {
    $categoria = Categoria::create([
        'nombre' => 'Electronica',
    ]);

    $desactivada = app(CategoriaService::class)
        ->desactivar($categoria->id_categoria);

    expect($desactivada->activa)->toBeFalse()
        ->and(Categoria::whereKey($categoria->id_categoria)->exists())
        ->toBeTrue();
});

test('se puede reactivar una categoria', function () {
    $categoria = Categoria::create([
        'nombre' => 'Electronica',
        'activa' => false,
    ]);

    $reactivada = app(CategoriaService::class)
        ->reactivar($categoria->id_categoria);

    expect($reactivada->activa)->toBeTrue();
});
