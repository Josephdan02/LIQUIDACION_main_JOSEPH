<?php

use App\Http\Controllers\AnexoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportarAnexoController;
use App\Http\Controllers\ImportarAnexoController;
use App\Http\Controllers\RegistroAnexoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('anexos', AnexoController::class);
Route::get('/registros/exportar', [ExportarAnexoController::class, 'filtrado'])->name('registros.exportar.filtrado');
Route::get('/registros/exportar-hoy', [ExportarAnexoController::class, 'hoy'])->name('registros.exportar.hoy');
Route::get('/registros/exportar-todo', [ExportarAnexoController::class, 'todo'])->name('registros.exportar.todo');
Route::post('/registros/exportar-seleccionados', [ExportarAnexoController::class, 'seleccionados'])->name('registros.exportar.seleccionados');
Route::get('/registros/{registro}/exportar-excel', [ExportarAnexoController::class, 'registroExcel'])->name('registros.exportar.excel');
Route::resource('registros', RegistroAnexoController::class);

Route::get('/importaciones/anexo-a', [ImportarAnexoController::class, 'create'])->name('importaciones.create');
Route::post('/importaciones/anexo-a', [ImportarAnexoController::class, 'store'])->name('importaciones.store');

Route::get('/anexos/{anexo}/exportar-excel', [ExportarAnexoController::class, 'excel'])->name('anexos.exportar.excel');
Route::get('/anexos/{anexo}/exportar-pdf', [ExportarAnexoController::class, 'pdf'])->name('anexos.exportar.pdf');
