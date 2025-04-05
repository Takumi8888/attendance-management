# お問い合わせフォーム

## 環境構築

<p>Dockerビルド</p>
<ol>
  <li>git clone https://github.com/Takumi8888/attendance-management.git</li>
  <li>cd attendance-management</li>
  <li>git remote set-url origin git@github.com:Takumi8888/attendance-management.git</li>
  <li>docker-compose up -d --build</li>
  <li>sudo chmod -R 777 src/*</li>
</ol>
<p>＊ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせて docker-compose.yml ファイルを編集してください。</p>

<p>Laravel環境構築</p>
<ol>
  <li>code .</li>
  <li>docker-compose exec php bash</li>
  <li>cp .env.example .env</li>
  <li>chmod 777 .env</li>
  <li>.envファイルの環境変数を変更</li>

```
APP_TIMEZONE=Asia/Tokyo

APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
APP_FAKER_LOCALE=ja_JP

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=mail
MAIL_PORT=1025
MAIL_USERNAME="info@example.com"
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="info@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

  <li>composer update</li>
  <li>php artisan key:generate</li>
  <li>php artisan migrate --seed</li>
</ol>

## 単体テスト
<p>下記手順にて単体テストを実施（phpコンテナ上にいる場合は、exitしてください）</p>

<p>MySQLコンテナ</p>

```
docker-compose exec mysql bash

mysql -u root -p
root

CREATE DATABASE demo_test;
SHOW DATABASES;

exit
exit
```

<p>PHPコンテナ</p>

```
docker-compose exec php bash

php artisan config:clear

vendor/bin/phpunit tests/Feature/Staff/RegisterTest.php
vendor/bin/phpunit tests/Feature/Staff/LoginTest.php
vendor/bin/phpunit tests/Feature/Staff/AttendanceRegisterTest.php
vendor/bin/phpunit tests/Feature/Staff/AttendanceTest.php
vendor/bin/phpunit tests/Feature/Staff/CorrectionRequestTest.php

vendor/bin/phpunit tests/Feature/Admin/LoginTest.php
vendor/bin/phpunit tests/Feature/Admin/AttendanceTest.php
vendor/bin/phpunit tests/Feature/Admin/CorrectionRequestTest.php
vendor/bin/phpunit tests/Feature/Admin/StaffTest.php
vendor/bin/phpunit tests/Feature/Admin/ApprovalTest.php
```

<p>注意：単体テストを実施する際は、1点ずつ実施してください。
<br/>Carbon::now()を実施する際、1秒のずれが生じる恐れがあります。（エラー例：下記参照）</p>

```
Failed asserting that a row in the table [break_times] matches the attributes {
    "work_time_id": 211,
    "start_time": "2025-04-05 18:03:53"
}.

Found similar results: [
    {
        "work_time_id": 211,
        "start_time": "2025-04-05 18:03:52"
    }
].
```

## 使用技術
<ul>
  <li>PHP 8.4.4</li>
  <li>Laravel 11.44.2</li>
  <li>jquery 3.7.1.min.js</li>
  <li>MySQL 8.0.26</li>
  <li>nginx 1.21.1</li>
</ul>

## URL
<ul>
  <li>開発環境：<a href="">http://localhost/</a></li>
  <li>phpMyAdmin：<a href="">http://localhost:8080/</a></li>
  <li>MailHog：<a href="">http://localhost:8025/</a></li>
</ul>

## テーブル仕様
### usersテーブル

| カラム名           | 型              | primary key | unique key | not null | foreign key |
| :---              | :---            | :---:       | :---:      | :---:    | :---        |
| id                | bigint unsigned | ○           |            | ○        |             |
| name              | varchar(50)     |             |            | ○        |             |
| email             | varchar(100)    |             | ○          | ○        |             |
| email_verified_at | timestamp       |             |            |          |             |
| password          | varchar(255)    |             |            | ○        |             |
| rememberToken     | varchar(100)    |             |            |          |             |
| created_at        | timestamp       |             |            |          |             |
| updated_at        | timestamp       |             |            |          |             |

### adminsテーブル

| カラム名           | 型              | primary key | unique key | not null | foreign key |
| :---              | :---            | :---:       | :---:      | :---:    | :---        |
| id                | bigint unsigned | ○           |            | ○        |             |
| name              | varchar(50)     |             |            | ○        |             |
| email             | varchar(100)    |             | ○          | ○        |             |
| email_verified_at | timestamp       |             |            |          |             |
| password          | varchar(255)    |             |            | ○        |             |
| rememberToken     | varchar(100)    |             |            |          |             |
| created_at        | timestamp       |             |            |          |             |
| updated_at        | timestamp       |             |            |          |             |

### work_timesテーブル

| カラム名           | 型              | primary key | unique key | not null | foreign key |
| :---              | :---            | :---:       | :---:      | :---:    | :---        |
| id                | bigint unsigned | ○           |            | ○        |             |
| user_id           | bigint unsigned |             |            | ○        | users(id)   |
| clock_in_time     | dateTime        |             |            | ○        |             |
| clock_out_time    | dateTime        |             |            |          |             |
| work_time         | time            |             |            |          |             |
| created_at        | timestamp       |             |            |          |             |
| updated_at        | timestamp       |             |            |          |             |

### break_timesテーブル

| カラム名           | 型              | primary key | unique key | not null | foreign key    |
| :---              | :---            | :---:       | :---:      | :---:    | :---           |
| id                | bigint unsigned | ○           |            | ○        |                |
| work_time_id      | bigint unsigned |             |            | ○        | work_times(id) |
| start_time        | dateTime        |             |            | ○        |                |
| end_time          | dateTime        |             |            |          |                |
| work_time         | time            |             |            |          |                |
| created_at        | timestamp       |             |            |          |                |
| updated_at        | timestamp       |             |            |          |                |

### attendancesテーブル

| カラム名              | 型              | primary key | unique key | not null | foreign key    |
| :---                 | :---            | :---:       | :---:      | :---:    | :---           |
| id                   | bigint unsigned | ○           |            | ○        |                |
| work_time_id         | bigint unsigned |             |            | ○        | work_times(id) |
| work_day             | date            |             |            | ○        |                |
| total_break_time     | time            |             |            |          |                |
| actual_working_hours | time            |             |            |          |                |
| created_at           | timestamp       |             |            |          |                |
| updated_at           | timestamp       |             |            |          |                |

### correction_requestsテーブル

| カラム名           | 型                   | primary key | unique key | not null | foreign key     |
| :---              | :---                 | :---:       | :---:      | :---:    | :---            |
| id                | bigint unsigned      | ○           |            | ○        |                 |
| attendance_id     | bigint unsigned      |             |            | ○        | attendances(id) |
| user_id           | bigint unsigned      |             |            | ○        | users(id)       |
| admin_id          | bigint unsigned      |             |            |          | admins(id)      |
| application_date  | date                 |             |            | ○        |                 |
| status            | tinyinteger unsigned |             |            | ○        |                 |
| note              | text                 |             |            | ○        |                 |
| created_at        | timestamp            |             |            |          |                 |
| updated_at        | timestamp            |             |            |          |                 |

## ER図
![alt](ER.png)

## テストアカウント
管理者<br/>
email: admin@coachtech.com<br/>
password: password
-------------------------
スタッフ<br/>
email: reina.n@coachtech.com<br/>
password: password
-------------------------

## 連絡事項
### 運営側に確認済みの項目
<ul>
  <li>環境構築について</li>
  <p>質問：任意の環境で構築しても問題ないか？
  <br/>回答：任意の環境構築で問題ありません。
	<br/>対応：任意の環境で構築した。
  </p>

  <li>テーブル数について</li>
  <p>質問：テーブル数を10個以内に収める際、既存で作成するテーブルは含まないという認識で良いか？
  <br/>　　　既存テーブルを削除すると動作しなくなる。
	<br/>回答：動作しないのであれば、既存テーブルは含まず、新規作成したテーブルをカウントする。
	<br/>対応：新規作成したテーブル数を10個以内に収める。
  </p>

  <li>管理者の登録画面について</li>
  <p>質問：管理者の登録画面がない
  <br/>回答：管理者の会員登録は行わない。ダミーデータで作成すること。
	<br/>対応：ダミーデータで対応した。
  </p>

  <li>勤怠詳細画面における休憩（追加入力分）について</li>
  <p>質問：休憩を2回以上とった場合、追加で入力フィールドを表示するとあるが、
	<br/>　　　figmaでは1回分の休憩でも追加の入力フィールドが確認でき、矛盾している。
  <br/>回答：休憩回数分のレコードと追加で１つ分の入力フィールドを表示する。
	<br/>対応：休憩回数に限らず、追加で1回分入力できるように実装した。
  </p>

  <li>FN022の機能要件について</li>
  <p>質問：「休憩入」ボタンは1日に何回でも押下できるとあるが、
  <br/>　　　「退勤は1日に1回だけ押下できる」が正しい表現であると思われる。
	<br/>回答：要件ミス。正しくは「退勤は1日に一回だけ押下できる」。
	<br/>対応：退勤は1日に一回だけ押下する仕様で実装した。
  </p>

  <li>FN038の機能要件について</li>
  <p>質問：「日付」「出勤・退勤」「休憩」「備考」の4項目を編集できるとあるが、
	<br/>　　　Figmaでは「出勤・退勤」「休憩」「備考」の3項目となっている。
  <br/>回答：日付の更新はできない仕様にする。
	<br/>対応：日付の更新はできない仕様にした。
  </p>
</ul>

### 運営側から回答待ちの項目
<ul>
  <li>対象：テストケースID：2、3</li>
	<p>質問：一般ユーザー、管理者共にログイン、ログアウトの処理テストがない。</p>

  <li>対象：テストケースID：6-1</li>
  <p>質問：出勤ボタンが押下された後のステータスは「勤務中」ではなく、「出勤中」である。</p>

  <li>対象：テストケースID：6-2</li>
	<p>質問：「勤務ボタン」が表示されないではなく、「出勤ボタン」である。</p>

  <li>対象：テストケースID：7-5、8-1</li>
	<p>質問：「勤務中」のユーザーではなく、「出勤中」である。</p>

  <li>対象：テストケースID：7-5</li>
	<p>質問：「休憩の日付」ではなく、「休憩時刻」である。</p>

  <li>対象：テストケースID：8-2</li>
	<p>質問：「退勤の日付」ではなく、「退勤時刻」である。</p>

  <li>対象：テストケースID：11-2、13-3</li>
	<p>質問：休憩開始時間に対して、before_or_equalのバリデーションルールは休憩終了時間
	<br/>　　　（end_time.*）を指定しなくてはいけないため、退勤時間を指定することはできない。
	<br/>　　　また、休憩終了時間におけるbefore_or_equalのバリデーションルールは退勤時間
	<br/>　　　（clock_out_time）を指定しているので、問題なくケアできている。以上の理由から、
	<br/>　　　勤務時間内の休憩判定をする場合、休憩開始と出勤時間、休憩終了時間と退勤時間
	<br/>　　　にて確認するほうが正しい。</p>

  <li>対象：テストケースID：11-2、11-3、13-3、13-4</li>
	<p>質問：「出勤時間もしくは退勤時間が不適切な値です」ではなく、
	<br/>　　　「休憩時間が勤務時間外です」が正しい。</p>

  <li>対象：テストケースID：11-7</li>
	<p>質問：勤怠詳細を修正し保存処理をした後に、管理者が承認をしないと申請一覧の
	<br/>　　　承認済みに表示されない。そのため、テスト手順として、「3.管理者が承認する」
	<br/>　　　「4.申請画面一覧を開く」「5.管理者が・・・」が正しい。</p>
</ul>