# 勤怠管理アプリ(attendance-management_app)

## 環境構築
Dockerビルド
1. git clone git@github.com:m-erui307/attendance-management_app.git
2. DockerDesktopアプリを立ち上げる
3. docker-compose up -d --build

Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. cp .env.example .env
4. .envに以下の環境変数を追加
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
5. アプリケーションキーの作成
php artisan key:generate
6. マイグレーションの実行
php artisan migrate
7. シーディングの実行
php artisan db:seed

## ログイン情報

### 管理者ユーザー
ログインURL: http://localhost/admin/login
メールアドレス: admin@example.com
パスワード: admin12345

### 一般ユーザー
ログインURL: http://localhost/login
メールアドレス: user@example.com
パスワード: user12345

※一般ユーザーは会員登録画面からも登録可能です。
会員登録URL: http://localhost/register

## 使用技術(実行環境)
- PHP8.1.34
- Laravel8.83.8
- MySQL8.0.26
- nginx1.21.1

## URL
- 開発環境：http://localhost/
- ユーザー登録：http://localhost/register
- ユーザーログイン：http://localhost/login
- 管理者ログイン：http://localhost/admin/login
- phpMyAdmin:：http://localhost:8080/

## ER図
![ER図](./docs/erd.png)

## その他
会員登録、ログイン、ログアウト等を一定数繰り返し行うと、HTTP 429 “TOO MANY REQUESTS”というエラーが発生します。こちらは短時間に多数のリクエストが送信されたことによるアクセス制限が原因と考えられます。そのため、お手数ですが上記のエラーが発生した際は一定時間時間を置くか、ブラウザのキャッシュクリアをしていただき、エラーが解消されるかどうかご確認ください。
それでもエラーが発生してしまう場合は、お知らせください。


