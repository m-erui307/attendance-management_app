<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠登録</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
    <nav class="header-nav">
      <ul class="header-nav-list">
        <li class="header-nav-item"><a href="">勤怠</a></li>
        <li class="header-nav-item"><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
        <li class="header-nav-item"><a href="">申請</a></li>
        <li>
          <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="header-logout">ログアウト</button>
          </form>
        </li>
      </ul>
    </nav>
  </header>
  <main>
    <div class="content">
      <div class="situation">
        @if (!$attendance || !$attendance->clock_in)
          勤務外
        @elseif ($attendance->clock_out)
          退勤済
        @elseif ($attendance->breaks()->whereNull('break_end')->exists())
          休憩中
        @else
          出勤中
        @endif
      </div>
      <div class="date" id="current-date">
      </div>
      <div class="time" id="current-time">
      </div>
      @if (!$attendance || !$attendance->clock_in)
      <form method="post" action="/attendance/start">@csrf
        <button class="clock-in_btn">出勤</button>
      </form>
      @elseif ($attendance->clock_out)
      <p class="clock-out_msg">
        お疲れ様でした。
      </p>
      @elseif ($attendance->breaks()->whereNull('break_end')->exists())
      <form method="post" action="/break/end">@csrf
        <button class="end-break_btn">休憩戻</button>
      </form>
      @else
      <div class="btn-content">
        <form method="post" action="/attendance/end">@csrf
          <button class="clock-out_btn">退勤</button>
        </form>
        <form method="post" action="/break/start">@csrf
          <button class="start-break_btn">休憩入</button>
        </form>
      </div>
      @endif
    </div>
  </main>
  <script>
  function updateDateTime() {
    const now = new Date();

    // 日付（年月日＋曜日）
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const date = now.getDate();

    const days = ['日', '月', '火', '水', '木', '金', '土'];
    const day = days[now.getDay()];

    document.getElementById('current-date').textContent =
      `${year}年${month}月${date}日(${day})`;

    // 時間（HH:MM）
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('current-time').textContent =
      `${hours}:${minutes}`;
  }

  // 初回表示
  updateDateTime();

  // 1分ごとに更新
  setInterval(updateDateTime, 60000);
  </script>
</body>
</html>