# SANTRIX Anexo Local

Aplicación Laravel para gestionar los 12 anexos contractuales, importar archivos Excel del Anexo A, registrar entregables y exportar resultados con la plantilla oficial.

## Funcionalidades

- Gestión de anexos (12 entregables contractuales).
- Registro y edición de filas del Anexo A.
- **Importación inteligente de Excel Anexo A** con vista previa, validación y detección de duplicados.
- Catálogo de 706 agentes de fiscalización para autocompletar por Código OSINERGMIN.
- Exportación Excel respetando la plantilla oficial `resources/plantillas/ANEXO_A_ITEM_6.xlsx`.
- Panel con métricas operativas.

## Requisitos

- PHP 8.3 o superior (extensiones: `pdo_mysql`, `zip`, `dom`, `gd`, `mbstring`, `intl`, `xml`, `fileinfo`)
- Composer
- Node.js 20 o superior
- MySQL 8 / MariaDB 10.6+

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura la base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=santrix_db
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos y ejecuta las migraciones:

```bash
mysql -u root -e "CREATE DATABASE santrix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
npm run build
```

## Ejecución

```bash
php artisan serve
```

Abre **http://127.0.0.1:8000** en el navegador.

## Cómo importar un Anexo A

1. Entra a **Importar Excel** en el menú lateral.
2. Selecciona el **anexo de destino** (1 a 12).
3. Sube el archivo Excel del Anexo A (`.xlsx` o `.xls`, máximo 10 MB).
4. Pulsa **Analizar archivo**.
5. Revisa la **vista previa**: filas detectadas, válidas, con errores, agentes encontrados y duplicados.
6. Pulsa **Confirmar importación**.

### Qué hace el importador

- Detecta la fila de encabezados buscando la columna `ITEM`.
- Reconoce las 19 columnas aunque cambien mayúsculas, tildes o espacios.
- Conserva `23.1` como texto (no lo convierte en `23.10`).
- Conserva ceros iniciales en `056`, `038`, etc.
- Interpreta fechas en formato `dd/mm/yyyy` y seriales de Excel.
- Busca cada Código OSINERGMIN en el catálogo de 706 agentes y **solo completa** lo que falte.
- Marca como error las filas sin Código Unidad o sin Código OSINERGMIN.
- Detecta duplicados reales (mismo anexo + OSINERGMIN + Código Unidad + documento + informe).
- Advierte si las fechas de los registros no coinciden con el período del anexo.
- **No crea anexos nuevos** y **no elimina registros existentes**.

## Estructura relevante

- `routes/web.php`: rutas de anexos, registros, importación y exportación.
- `app/Http/Controllers/ImportarAnexoController.php`: flujo de importación (formulario → vista previa → confirmar).
- `app/Services/Anexo/AnexoImportService.php`: validación, duplicados y guardado.
- `app/Services/Anexo/AnexoExcelMapper.php`: lectura y mapeo de columnas.
- `app/Services/Anexo/AgenteFiscalizacionLookup.php`: catálogo de agentes (solo lectura).
- `app/Services/Excel/AnexoAExcelExportService.php`: exportación con plantilla oficial.
- `resources/plantillas/`: plantilla oficial y archivos de referencia.

## Pruebas

```bash
php artisan test
```

Las pruebas usan la base de datos `santrix_test` (MySQL), que se crea automáticamente y **no afecta** a `santrix_db`.

## Preparación para GitHub

El proyecto ignora archivos locales y generados:

- `.env`
- `node_modules/`
- `vendor/`
- `public/build/`
- `storage/logs/`
- Archivos temporales de Excel (`~$*.xlsx`)

## Publicación

```bash
git init -b main
git add .
git commit -m "Initial commit"
git remote add origin <TU_REPOSITORIO>
git push -u origin main
```
