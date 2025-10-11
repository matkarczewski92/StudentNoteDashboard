<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    @page { size: A4 portrait; margin: 12mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 8px 0; }
    .meta { font-size: 12px; color: #555; margin-bottom: 12px; }
    .grid { column-count: 2; column-gap: 16px; }
    .row { break-inside: avoid; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dotted #999; padding: 4px 2px; min-height: 18px; }
    .name { flex: 1; margin-right: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sign { width: 160px; text-align: right; color: #999; }
    .footer { position: fixed; bottom: 10mm; left: 12mm; right: 12mm; font-size: 10px; color: #777; }
    @media print { .noprint { display: none; } }
    .noprint { margin-bottom: 10px; }
  </style>
  <script>function doPrint(){ window.print(); }</script>
  </head>
<body>
  <div class="noprint">
    <button onclick="doPrint()">Drukuj / Zapisz do PDF</button>
  </div>
  <h1>{{ $title }}</h1>
  <div class="meta">Data: {{ now()->format('Y-m-d') }} • Liczba osób: {{ $group->users->count() }}</div>

  <div class="grid">
    @foreach($group->users as $u)
      <div class="row">
        <div class="name">{{ $u->name }} — nr albumu: {{ $u->album ?? '—' }}</div>
        <div class="sign">....................................</div>
      </div>
    @endforeach
  </div>

  <div class="footer">Wygenerowano automatycznie • StudentNoteDashboard</div>
</body>
</html>
