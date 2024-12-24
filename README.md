# パッケージ説明

このパッケージは、日本の郵便番号データを管理・操作するための機能を提供します。例えば、郵便番号データのインポート、検索、および関連処理を簡単に実行できます。このパッケージを利用することで、郵便番号に関連する処理を効率化し、開発プロセスをスムーズにすることができます。

---

## 使用方法

パッケージを使用する際は、以下のコマンドを実行してください。

```bash
php artisan postcode:create
```

このコマンドを実行することで、郵便番号に関連する初期設定およびデータのセットアップが行われます。この処理により、郵便番号データの利用準備が整います。

---

## 郵便番号の検索

次のコードにより、郵便番号の検索を行うことができます。

```php
$model = PostCode::search('郵便番号')->first();
```