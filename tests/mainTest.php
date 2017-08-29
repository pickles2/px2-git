<?php
/**
 * test for px2-git
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $px2git;
	private $path_git_home;
	private $path_entry_script;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();

		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();

		$this->path_git_home = __DIR__.'/testdata/git_home/';
		$this->path_entry_script = __DIR__.'/testdata/git_home/.px_execute.php';

		if( is_file($this->path_entry_script) ){
			$this->px2git = new tomk79\pickles2\git\main( $this->path_entry_script );
		}
	}


	/**
	 * 環境セットアップ
	 */
	public function testSetupEnv(){
		$this->fs->copy_r(
			__DIR__.'/testdata/htdocs/',
			__DIR__.'/testdata/git_home/'
		);
	}


	/**
	 * 基本テストパターン
	 */
	public function testBasicPattern(){

		// --------------------------------------
		// リポジトリを初期化
		$result = $this->px2git->init( $this->path_git_home );
		$this->assertTrue( $result );
		$this->assertTrue( $this->fs->is_dir( $this->path_git_home.'.git' ) );
		$this->assertEquals( $this->px2git->get_path_git_home(), $this->path_git_home );

		// 初期化エラーのテスト
		$result = $this->px2git->init( $this->path_git_home );
		$this->assertFalse( $result ); // 初期化済みの場合は失敗する


		// --------------------------------------
		// 全ファイルをコミット
		$this->px2git->commit_all('initial commit. (test)');


		// --------------------------------------
		// サイトマップを編集してコミット
		$this->assertTrue( $this->fs->copy(
			__DIR__.'/testdata/sample_data/sitemaps/b/sitemap.csv',
			$this->path_git_home.'/px-files/sitemaps/sitemap.csv'
		) );

		$status = $this->px2git->status();
		// var_dump($status);
		$this->assertEquals( $status['div']['sitemaps'][0]['file'], 'px-files/sitemaps/sitemap.csv' );
		$this->assertEquals( $status['div']['sitemaps'][0]['work_tree'], 'M' );
		$this->assertEquals( $status['div']['sitemaps'][1]['file'], 'px-files/sitemaps/sitemap.xlsx' );
		$this->assertEquals( $status['div']['sitemaps'][1]['work_tree'], 'M' );

		$this->px2git->commit_sitemaps('commit sitemaps - b');

		$log = $this->px2git->log();
		// var_dump($log);

		$this->assertTrue( $this->fs->copy(
			__DIR__.'/testdata/sample_data/sitemaps/a/sitemap.csv',
			$this->path_git_home.'/px-files/sitemaps/sitemap.csv'
		) );

		$status = $this->px2git->status();
		// var_dump($status);
		$this->assertEquals( $status['div']['sitemaps'][0]['file'], 'px-files/sitemaps/sitemap.csv' );
		$this->assertEquals( $status['div']['sitemaps'][0]['work_tree'], 'M' );
		$this->assertEquals( $status['div']['sitemaps'][1]['file'], 'px-files/sitemaps/sitemap.xlsx' );
		$this->assertEquals( $status['div']['sitemaps'][1]['work_tree'], 'M' );

		$this->px2git->commit_sitemaps('commit sitemaps - a');

		$log = $this->px2git->log();
		// var_dump($log);
		$this->assertNotEmpty( $log );
		$this->assertEquals( count($log), 3 );
		$this->assertEquals( $log[2]['title'], 'initial commit. (test)' );

		// $this->git = new \PHPGit\Git();
		// $this->git->setRepository( $this->path_git_home );
		// var_dump($this->git->log());


		// --------------------------------------
		// コミットの詳細を取得する
		$hash = $log[2]['hash'];
		$last_commit = $this->px2git->show( $hash );
		// var_dump($hash);
		// var_dump($last_commit);
		$this->assertEquals( gettype($last_commit['plain']), gettype('') );


		// --------------------------------------
		// ブランチの一覧を取得する
		$branches = $this->px2git->branch_list();
		$this->assertTrue( is_array($branches['master']) );
		$this->assertTrue( is_null(@$branches['testbranch']) );

		// --------------------------------------
		// ブランチ "testbranch" を作成する
		$this->assertTrue( $this->px2git->create_branch('testbranch') );
		$branches = $this->px2git->branch_list();
		// var_dump($branches);
		$this->assertTrue( is_array($branches['master']) );
		$this->assertTrue( is_array($branches['testbranch']) );


	} // testBasicPattern()

}
