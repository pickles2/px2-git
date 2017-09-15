# pickles2/px2-git

Pickles 2 のプロジェクトデータに最適化された git 関連APIを提供します。

## API List

`docs/phpdoc/index.html` を参照してください。

## Change log

### pickles2/px2-git@2.0.0-beta.3 (2017-??-??)

- 対象リポジトリが submodule である場合に対応した。
- 幾つかの細かい不具合の修正。

### pickles2/px2-git@2.0.0-beta.2 (2016-08-24)

- `$px2git->show()` の最大文字数制限を追加。
- `$px2git->show()` の返却値を、文字列から配列に変更。
- `$px2git->branch_list()` 追加。
- `$px2git->create_branch()` 追加。
- `$px2git->commit_all()` 追加。
- 深い階層の untracked-files をコミットできない不具合を修正。

### pickles2/px2-git@2.0.0-beta.1 (2016-06-15)

- initial release.

## ライセンス - License

Copyright (c)2001-2016 Tomoya Koyanagi, and Pickles 2 Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
