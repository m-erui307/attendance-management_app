<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_attendance-list.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
    <nav class="header-nav">
      <ul class="header-nav-list">
        <li class="header-nav-item"><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
        <li class="header-nav-item"><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
        <li class="header-nav-item"><a href="">申請一覧</a></li>
        <li>
          <form action="{{ route('admin.logout') }}" method="post">
            @csrf
            <button class="header-logout">ログアウト</button>
          </form>
        </li>
      </ul>
    </nav>
  </header>
  <main>
    <div class="content">
      <h2 class="title">{{ $targetDate->format('Y年n月j日') }}の勤怠</h2>
      <div class="calendar-nav">
        <a class="last-month_btn"
     href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}"><span class="arrow">←</span>前日</a>
        <div class="calendar">
          <img class="calendar-icon" src="../../../img/calendar-icon.png" alt="カレンダー">
          {{ $targetDate->format('Y/m/d') }}
        </div>
        <a class="next-month_btn"
     href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日<span class="arrow">→</span></a>
      </div>
      <table class="attendance-table">
        <thead>
          <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach($calendar as $item)
          @if($item['attendance'])
          <tr>
            <td>{{ $item['user']->name }}</td>
            <td>{{ $item['attendance']?->clock_in?->format('H:i') }}</td>
            <td>{{ $item['attendance']?->clock_out?->format('H:i') }}</td>
            <td>{{ $item['attendance']?->break_time }}</td>
            <td>{{ $item['attendance']?->total_time }}</td>
            <td><a class="table_detail" href="{{ route('admin.attendance.show',
    ['user' => $item['user']->id,
    'date' => $targetDate->format('Y-m-d')]
) }}">詳細</a></td>
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>