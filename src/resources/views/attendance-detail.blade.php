<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠詳細</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
    <nav class="header-nav">
      <ul class="header-nav-list">
        <li class="header-nav-item"><a href="">勤怠</a></li>
        <li class="header-nav-item"><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
        <li class="header-nav-item"><a href="{{ route('request.list') }}">申請</a></li>
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
      <h2 class="title">勤怠詳細</h2>
      @php
    // 未出勤日対策
    $attendance = $attendance ?? null;
    $user = $user ?? auth()->user();
    $date = $date ?? now();
    $breaks = $breaks ?? [];
    $breakCount = count($breaks);
  @endphp
      <form action="{{ route('request.store') }}" method="post">
    @csrf
    <input type="hidden" name="target_date" value="{{ $date->format('Y-m-d') }}">
    <input type="hidden" name="attendance_id" value="{{ $attendance?->id }}">
      <table class="detail-table">
        <tr>
          <th>名前</th>
          <td>{{ optional(auth()->user())->name }}</td>
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
            @if($pendingRequest)
    {{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
    <span class="tilde">〜</span>
    {{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
@else
    <input type="time" name="clock_in"
        value="{{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
    <span class="tilde">〜</span>
    <input type="time" name="clock_out"
        value="{{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
@endif
          </td>
        </tr>
        @php
    $loopCount = $pendingRequest ? $breakCount : $breakCount + 1;
@endphp

@for($i = 0; $i < $loopCount; $i++)
        <tr>
          <th>
            {{ $i === 0 ? '休憩' : '休憩'.($i+1) }}
          </th>
          <td>
            @if($pendingRequest)
    {{ $breaks[$i]['break_start'] ?? '' }}
    <span class="tilde">〜</span>
    {{ $breaks[$i]['break_end'] ?? '' }}
@else
    <input type="time" name="breaks[{{ $i }}][start]"
        value="{{ $breaks[$i]['break_start'] ?? '' }}">
    <span class="tilde">〜</span>
    <input type="time" name="breaks[{{ $i }}][end]"
        value="{{ $breaks[$i]['break_end'] ?? '' }}">
@endif
          </td>
        </tr>
          @endfor
        <tr>
          <th>備考</th>
          <td>
            @if($pendingRequest)
    {{ $attendance->remark ?? '' }}
@else
    <textarea class="remark" name="remark" rows="3">
        {{ old('remark', $attendance->remark ?? '') }}
    </textarea>
@endif
          </td>
        </tr>
      </table>
      @if($pendingRequest)
    <p class="request-msg">＊承認待ちのため修正はできません。</p>
@else
      <button class="edit_btn">修正</button>
      @endif
    </div>
  </main>
</body>
</html>