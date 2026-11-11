<?php

return [
    'title' => 'RESUMEN DE LOS SERVICIOS Y/O ACTIVIDADES DE SUPERVISION Y/O FISCALIZACION',

    'base_entregable' => [
        'empresa_supervisora' => 'EVALUACIUON Y SUPERVISION EN ENERGIA E.I.R.L.',
        'ruc' => '20601266980',
        'numero_contrato' => 'SUP2400158',
        'division' => 'HUANUCO',
        'nombre_servicio' => 'SUPERVISIÓN Y FISCALIZACIÓN DEL SUBSECTOR HIDROCARBUROS: HUANUCO',
        'periodo_inicio' => '2024-11-18',
        'periodo_fin' => '2024-12-17',
        'fecha_ingreso_entregable' => '2024-12-17',
        'responsable' => null,
        'estado' => 'activo',
    ],

    'entregable_labels' => [
        'empresa_supervisora' => 'EMPRESA',
        'ruc' => 'RUC',
        'numero_contrato' => 'CONTRATO',
        'nombre_servicio' => 'NOMBRE DEL SERVICIO CONTRATADO',
        'periodo' => 'PERIODO DE EJECUCION CONTRACTUAL',
        'fecha_ingreso_entregable' => 'FECHA CONTRACTUAL PARA INGRESO DE ENTREGABLE',
    ],

    'column_labels' => [

        'codigo_unidad' => 'CODIGO UNIDAD',

        'item' => 'ITEM',

        'codigo_osinergmin' => 'CODIGO OSINERGMIN',

        'expediente_siged' => 'EXPEDIENTE SIGED',

        'numero_documento' => 'NUMERO DOCUMENTO',

        'fecha_asignacion' => 'FECHA ASIGNACION',

        'codigo_actividad' => 'CODIGO ACTIVIDAD',

        'razon_social_agente' => 'RAZON SOCIAL AGENTE',

        'tipo_supervision' => 'TIPO SUPERVISION',

        'tipo_entregable' => 'TIPO ENTREGABLE',

        'numero_informe' => 'NUMERO INFORME',

        'visitado' => 'VISITADO',

        'fecha_visita' => 'FECHA VISITA',

        'fecha_derivacion' => 'FECHA DERIVACION',

        'personal_es' => 'PERSONAL ES',

        'estado_entregable' => 'ESTADO ENTREGABLE',

        'observaciones' => 'OBSERVACIONES',

        'comentarios' => 'COMENTARIOS',

        'observaciones_condiciones' => 'OBSERVACIONES CONDICIONES',

    ],

    'estado_entregable_options' => [
        'EN PROCESO',
        'CONCLUIDO',
        'OBSERVADO',
        'PENDIENTE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Codigos de unidad (Anexo A)
    |--------------------------------------------------------------------------
    |
    | Catalogo de opciones para el campo codigo_unidad. Se guarda el texto
    | completo de la opcion, tal como se viene almacenando en la base de datos.
    |
    */
    'codigo_unidad_options' => [
        '23.1 Atención de solicitudes en el otorgamiento del Registro de Hidrocarburos en instalaciones (Acción de Fiscalización: Solicitud atendida).',
        '28.1 Atención de solicitudes de otorgamiento del Registro de Hidrocarburos en Medios de Transporte Hidrocarburos.',
        '23.2 Atención de solicitudes de informe técnico favorable en Instalaciones (Acción de Fiscalización: Solicitud atendida).',
        '23.3 Atención de solicitudes de Informe técnico favorable de diseño, fin de construcción (GNV, GNC, GNL) (Acción de Fiscalización: Solicitud atendida).',
        '23.4 Atención de solicitudes de actas de verificación de pruebas y de conformidad (Acción de Fiscalización: Solicitud atendida).',
        '23.5 Atención de denuncias de Hidrocarburos (Instalaciones) (Acción de Fiscalización: Solicitud atendida).',
        '24.1 Fiscalización PRICE (Acción de Fiscalización: Informe de Fiscalización).',
        '24.2 Fiscalización RIC (GNV, GNC. GNL) (Acción de Fiscalización: Informe de Fiscalización).',
        '24.3 Fiscalización de Control Volumétrico (Acción de Fiscalización: Informe de Fiscalización).',
        '24.4 Fiscalización GPS (Acción de Fiscalización: Informe de Fiscalización).',
        '24.5 Fiscalización SCOP (Acción de Fiscalización: Informe de Fiscalización).',
        '24.6 Fiscalización SPIC (Acción de Fiscalización: Informe de Fiscalización).',
        '24.7 Fiscalización de obligaciones respecto al envasado, pintado y canje de cilindros de GLP (Acción de Fiscalización: Informe de Fiscalización).',
        '24.8 Fiscalización por agentes no autorizados de oficio (Acción de Fiscalización: Informe de Fiscalización).',
        '24.9 Fiscalización por agentes no autorizados de comiso de Equipos y Facilidades de despacho de combustibles (Acción de Fiscalización: Informe de Fiscalización).',
        '24.10 Fiscalización especial - Apoyo en la lucha contra la minería ilegal (Acciones de Fiscalización: Informe de Fiscalización).',
        '24.11 Fiscalización Orientativa (Acción de Fiscalización: Informe de Fiscalización).',
        '24.12 Fiscalización de Pólizas (Acción de Fiscalización: Informe de Fiscalización).',
        '24.13 Fiscalización por empadronamiento de transporte con GPS (Acción de Fiscalización: Informe de Fiscalización).',
        '25.1 Fiscalización de seguridad por criticidad de instalaciones (Acción de Fiscalización: Informe de Fiscalización).',
        '25.2 Fiscalización Operativa de Seguridad de LV GLP (Acción de Fiscalización: Informe de Fiscalización).',
        '25.3 Fiscalización Operativa de Seguridad de CD y RD (Acción de Fiscalización: Informe de Fiscalización).',
        '25.4 Fiscalización operativa de certificación y seguridad de CD, RD y LV de GLP (Acción de Fiscalización: Informe de Fiscalización).',
        '25.6 Fiscalización operativa de los demás agentes (CDMV, Trans. Acuático, CM y otros*) (Acción de Fiscalización: Informe de Fiscalización).',
        '28.5 Fiscalización operativa de los demás agentes (Trans. Acuático).',
        '25.7 Fiscalización operativa de actos inseguros (Acción de Fiscalización: Informe de Fiscalización).',
        '25.8 Fiscalización operativa PDJ (Acción de Fiscalización: Informe de Fiscalización).',
        '28.7 Fiscalización operativa a medios de transporte terrestre de CL.',
        '28.8 Fiscalización operativa a medios de transporte GLP en cilindros.',
        '28.9 Fiscalización operativa Transporte-Distribución a Granel de GLP.',
        '28.10 Fiscalización Operativa a medios de transporte y unidades móviles de GNC y GNL.',
        '25.9 Fiscalización operativa a los agentes con instalaciones fijas de GNV, GNC y GNL (Acción de Fiscalización: Informe de Fiscalización).',
        '25.10 Fiscalización convencional de emergencias (Acción de Fiscalización: Informe de Fiscalización).',
        '26.1 Fiscalización para el control de calidad de los combustibles / GLP despachados / producidos en establecimientos de venta al público (muestrales) (Acción de Fiscalización: Informe de Fiscalización).',
        '26.2 Fiscalización para el control de calidad de los combustibles / GLP despachados / producidos en plantas de abastecimiento o refinerías (censales) (Acción de Fiscalización: Informe de Fiscalización).',
        '26.6 Fiscalización para el control de calidad de los combustibles / GLP despachados en establecimientos de venta al público, plantas de abastecimiento o refinerías (especiales o de parte) (Acción de Fiscalización: Informe de Fiscalización).',
        '26.8 Fiscalización de la calidad de los combustibles a los vehículos de Transporte Terrestre Internacional que ingresan al territorio nacional cumpliendo la normatividad peruana en materia de calidad de combustibles(*) (Acción de Fiscalización: Informe de Fiscalización).',
        '26.3 Fiscalización metrológica de combustibles despachados en establecimientos de venta al público (muestrales / censales) (Acción de Fiscalización: Informe de Fiscalización).',
        '26.4 Fiscalización para el control de peso del GLP envasado en plantas u otros centros de distribución o comercialización (Acción de Fiscalización: Informe de Fiscalización).',
        '26.5 Fiscalización del etiquetado de balones (Acción de Fiscalización: Informe de Fiscalización).',
        '26.7 Fiscalización metrológica o de peso en establecimientos de venta al público o plantas u otros centros de comercialización de combustibles líquidos o GLP (especiales o de parte) (Acción de Fiscalización: Informe de Fiscalización).',
    ],
];