<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_approval.css') }}">
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
      <table class="detail-table">
        <tr>
          <th>名前</th>
          <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
          <th>日付</th>
          <td class="date-cell">
  <span class="year">{{ \Carbon\Carbon::parse($request->target_date)->format('Y年') }}</span>
  <span class="md">{{ \Carbon\Carbon::parse($request->target_date)->format('n月j日') }}</span>
</td>
        </tr>
        <tr>
          <th>出勤・退勤</th>
          <td>
            {{ $request->clock_in ? \Carbon\Carbon::parse($request->clock_in)->format('H:i') : '-' }}
<span class="tilde">〜</span>
{{ $request->clock_out ? \Carbon\Carbon::parse($request->clock_out)->format('H:i') : '-' }}
          </td>
        </tr>
        @php
    $breakCount = count($breaks);
@endphp

@for($i = 0; $i < $breakCount + 1; $i++)
        <tr>
          <th>
            @if($i === 0)
            休憩
        @else
            休憩{{ $i + 1 }}
        @endif
    </th>
          <td>
            @if(isset($breaks[$i]))
            {{ \Carbon\Carbon::parse($breaks[$i]['start'])->format('H:i') }}
            <span class="tilde">〜</span>
            {{ \Carbon\Carbon::parse($breaks[$i]['end'])->format('H:i') }}
        @else
            {{-- データなしの場合は空欄 --}}
        @endif
          </td>
        </tr>
        @endfor
        <tr>
          <th>備考</th>
          <td>
            {{ $request->remark }}
          </td>
        </tr>
      </table>
      <form method="post" action="{{ route('admin.request.approve', $request->id) }}">
  @csrf
  @method('PUT')
  @if($request->status === 'approved')
  <button class="approved_btn" disabled>承認済み</button>
  @else
  <button class="approval_btn">承認</button>
  @endif
</form>
    </div>
  </main>
</body>
</html>