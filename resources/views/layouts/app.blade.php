<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'SANTRIX ANEXO LOCAL')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,700" rel="stylesheet" />
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style>
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f1f5f9;
    color:#1e293b;
}
.sidebar{
    width:250px;
    background:#0f172a;
    color:white;
    min-height:100vh;
    padding:20px;
    position:fixed;
}
.content{
    margin-left:270px;
    padding:30px;
}
.card{
    background:white;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}
.btn{
    display:inline-block;
    padding:10px 15px;
    background:#0ea5e9;
    color:white;
    border-radius:10px;
    text-decoration:none;
}
table{
    width:100%;
    border-collapse:collapse;
    background:white;
}
table th{
    background:#0f172a;
    color:white;
    padding:12px;
}
table td{
    padding:10px;
    border-bottom:1px solid #ddd;
}
</style>
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-800">
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-slate-200 bg-slate-950 text-slate-100 lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r lg:border-slate-800">
                <div class="flex items-center justify-between px-6 py-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">SANTRIX</p>
                        <h1 class="mt-2 font-display text-2xl font-bold">ANEXO LOCAL</h1>
                        <p class="mt-2 text-sm text-slate-400">Gestión local del Anexo A en Laravel + SQLite.</p>
                    </div>
                </div>

                <nav class="space-y-2 px-4 pb-6">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                    <a href="{{ route('anexos.index') }}" class="nav-link {{ request()->routeIs('anexos.*') ? 'nav-link-active' : '' }}">Configuración del Anexo</a>
                    <a href="{{ route('registros.index') }}" class="nav-link {{ request()->routeIs('registros.*') ? 'nav-link-active' : '' }}">Registros Anexo A</a>
                    <a href="{{ route('importaciones.create') }}" class="nav-link {{ request()->routeIs('importaciones.*') ? 'nav-link-active' : '' }}">Importar Excel</a>
                </nav>
            </aside>

            <div class="flex-1">
                <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-8">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Sistema local</p>
                            <h2 class="font-display text-2xl font-bold text-slate-900">@yield('page-title', 'SANTRIX ANEXO LOCAL')</h2>
                        </div>
                        <div class="rounded-full border border-cyan-100 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700">
                            {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <p class="font-semibold">Revisa los campos del formulario.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
