<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠一覧</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
    <nav class="header-nav">
      <ul class="header-nav-list">
        <li class="header-nav-item"><a href="">勤怠</a></li>
        <li class="header-nav-item"><a href="">勤怠一覧</a></li>
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
      <h2 class="title">申請一覧</h2>
      <div class="border">
        <ul class="border-list">
          <li><a class="border-list__btn" href="">承認待ち</a></li>
          <li><a class="border-list__btn" href="">承認済み</a></li>
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
          
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="table_detail" href="">詳細</a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>