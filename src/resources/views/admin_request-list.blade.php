<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_request-list.css') }}">
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
      <h2 class="title">申請一覧</h2>
      <div class="border">
        <ul class="border-list">
          <li><a class="border-list__btn {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('admin.request.list', ['status' => 'pending']) }}">承認待ち</a></li>
          <li><a class="border-list__btn {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('admin.request.list', ['status' => 'approved']) }}">承認済み</a></li>
        </ul>
      </div>
      <table class="request-table">
        <thead>
          <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $request)
          <tr>
            <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse($request->target_date)->format('Y/m/d') }}</td>
            <td>{{ $request->remark }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td><a class="table_detail" href="{{ route('admin.request.show', $request->id) }}">詳細</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>