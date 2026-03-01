<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠詳細</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_attendance-detail.css') }}">
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
      <h2 class="title">勤怠詳細</h2>
      @php
    // 未出勤日対策
    $attendance = $attendance ?? null;
    $user = $user ?? auth()->user();
    $date = $date ?? now();
    $breaks = $attendance?->breaks ?? collect();
    $breakCount = $breaks->count();
  @endphp
      <form action="{{ route('admin.attendance.update',
    ['user' => $user->id,
    'date' => $date->format('Y-m-d')]) }}" method="post">
    @csrf
    @method('PUT')
      <table class="detail-table">
        <tr>
          <th>名前</th>
          <td>{{ $user->name }}</td>
        </tr>
        <tr>
          <th>日付</th>
          <td class="date-cell">
  <span class="year">{{ $date->format('Y年') }}</span>
  <span class="md">{{ $date->format('n月j日') }}</span>
</td>
        </tr>
        <tr>
          <th>出勤・退勤</th>
          <td>
            <input type="time" name="clock_in"
  value="{{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
<span class="tilde">〜</span>
<input type="time" name="clock_out"
  value="{{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
          </td>
        </tr>
        @for($i = 0; $i < $breakCount + 1; $i++)
        <tr>
          <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
          <td>
            <input type="time" name="breaks[{{ $i }}][start]"
          value="{{ isset($breaks[$i]['break_start']) ? $breaks[$i]['break_start']->format('H:i') : '' }}">
          <span class="tilde">〜</span>
          <input type="time" name="breaks[{{ $i }}][end]"
          value="{{ isset($breaks[$i]['break_end']) ? $breaks[$i]['break_end']->format('H:i') : '' }}">
          </td>
        </tr>
        @endfor
        <tr>
          <th>備考</th>
          <td>
            <textarea class="remark" name="remark" rows="3">
  </textarea>
          </td>
        </tr>
      </table>
      <button class="edit_btn">修正</button>
    </div>
  </main>
</body>
</html>