<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_attendance-staff.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
    <nav class="header-nav">
      <ul class="header-nav-list">
        <li class="header-nav-item"><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
        <li class="header-nav-item"><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
        <li class="header-nav-item"><a href="{{ route('admin.request.list') }}">申請一覧</a></li>
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
      <h2 class="title">{{ $user->name }}さんの勤怠</h2>
      <div class="calendar-nav">
        <a class="last-month_btn"
     href="{{ route('admin.staff.show', [
    'user' => $user->id,'month' => $prevMonth]) }}"><span class="arrow">←</span>前月</a>
        <div class="calendar">
          <img class="calendar-icon" src="../../../img/calendar-icon.png" alt="カレンダー">
          {{ $targetMonth->format('Y/m') }}
        </div>
        <a class="next-month_btn"
     href="{{ route('admin.staff.show', ['user' => $user->id,'month' => $nextMonth]) }}">翌月<span class="arrow">→</span></a>
      </div>
      <table class="attendance-table">
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach($calendar as $item)
          @php $attendance = $item['attendance']; @endphp
          <tr>
            <td>{{ $item['date']->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
            <td>{{ $attendance?->clock_in?->format('H:i') }}</td>
            <td>{{ $attendance?->clock_out?->format('H:i') }}</td>
            <td>{{ $attendance?->break_time }}</td>
            <td>{{ $attendance?->total_time }}</td>
            <td><a class="table_detail" href="{{ route('admin.attendance.show', [
    'user' => $user->id,
    'date' => $item['date']->format('Y-m-d')
]) }}">詳細</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <a class="csv-btn" href="{{ route('admin.staff.attendance.csv', ['user' => $user->id, 'month' => $targetMonth->format('Y-m')]) }}">CSV出力</a>
    </div>
  </main>
</body>
</html>