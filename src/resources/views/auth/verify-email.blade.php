<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メール認証</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
</head>
<body>
  <header class="header">
    <img class="header-logo" src="../../../img/COACHTECHヘッダーロゴ.png" alt="COACHTECH">
  </header>
  <main>
    <div class="verification-content">
      <div class="verification-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br />メール認証を完了してください。
      </div>
      <a href="http://localhost:8025" target="_blank" class="verification-btn">
        認証はこちらから
      </a>
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="verification-resend" type="submit">
          認証メールを再送する
        </button>
      </form>
    </div>
  </main>
</body>
</html>