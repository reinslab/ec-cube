## EC-CUBE3 プラグインのインストールについて

### 概要
本家EC-CUBEのリポジトリでは、app/Plugin/はgit管理対象外であるが、当社においては、
プラグイン導入及び開発の効率を考慮し、app/Pluginは、git管理対象とする。
ローカルでテストを完了したプラグインは、EC-CUBE3管理画面においてプラグインを
インストールした状態で、githubにpushする。

app/Pluginをgit管理対象にするため、下記を.gitignoreに記載済みである。
※.gitignore自体もgit管理対象であるため、fetch及びmergeすることにより
　各開発メンバーのローカル環境においてもapp/Pluginは、git管理対象となる。

```
!/app/Plugin/*
```

各開発メンバーは、githubのリポジトリをfetch及びmerge後、
以下の手順にて、プラグインのインストール及び有効化を行う。

### コマンドラインによるPluginのインストール
githubからfetch及びmergeした状態では、管理画面のオーナーズストア/プラグイン/
プラグイン一覧で、「未確認プラグイン」として表示される。
（プラグインのファイルは存在するが、データベースに登録されていない状態）

「未確認プラグイン」を下記のコマンドにてインストールし、有効化を行う。

基本形
```
php app/console plugin:develop install    //インストール
php app/console plugin:develop enable     //有効化
php app/console plugin:develop disable    //無効化
php app/console plugin:develop uninstall  //アンインストール
php app/console plugin:develop update     //アップデート
```
インストール例
```
php app/console plugin:develop install --code=Display
```
有効化例
```
php app/console plugin:develop enable --code=Display
```
無効化例
```
php app/console plugin:develop disable --code=Display
```
アンインストール例
```
php app/console plugin:develop uninstall --code=Display
```
※Displayの部分は、プラグインのcode名を記載する。

以上
