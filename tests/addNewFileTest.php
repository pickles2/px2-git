<?php
/**
 * test for px2-git
 */
class addNewFileTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $px2git;
	private $path_git_home;
	private $path_entry_script;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();

		// $result = $this->fs->rmdir_r( __DIR__.'/testdata/git_home/' );
		// if(!$result){
		// 	echo "FAILED to remove directory: ".__DIR__.'/testdata/git_home/'."\n";
		// }

		$this->fs->copy_r(
			__DIR__.'/testdata/htdocs/',
			__DIR__.'/testdata/git_home/'
		);

		$this->path_git_home = __DIR__.'/testdata/git_home/';
		$this->path_entry_script = __DIR__.'/testdata/git_home/.px_execute.php';

		$this->px2git = new tomk79\pickles2\git\main( $this->path_entry_script );
	}


	/**
	 * 基本テストパターン
	 */
	public function testBasicPattern(){

		// --------------------------------------
		// リポジトリを初期化
		$this->px2git->init( $this->path_git_home );
		$this->assertTrue( $this->fs->is_dir( $this->path_git_home.'.git' ) );
		$this->assertEquals( $this->px2git->get_path_git_home(), $this->path_git_home );


		// --------------------------------------
		// サイトマップを編集してコミット
		$this->assertTrue( $this->fs->copy(
			__DIR__.'/testdata/sample_data/sitemaps/b/sitemap.csv',
			$this->path_git_home.'/px-files/sitemaps/sitemap.csv'
		) );

		$status = $this->px2git->status();
		// var_dump($status);
		$this->assertEquals( $status['div']['sitemaps'][0]['file'], 'px-files/sitemaps/sitemap.csv' );
		$this->assertEquals( $status['div']['sitemaps'][0]['work_tree'], '?' );
		$this->assertEquals( $status['div']['sitemaps'][1]['file'], 'px-files/sitemaps/sitemap.xlsx' );
		$this->assertEquals( $status['div']['sitemaps'][1]['work_tree'], '?' );

		$this->px2git->commit_sitemaps('commit sitemaps - b');

		$log = $this->px2git->log();
		// var_dump($log);

		$this->assertTrue( $this->fs->copy(
			__DIR__.'/testdata/sample_data/sitemaps/a/sitemap.csv',
			$this->path_git_home.'/px-files/sitemaps/sitemap.csv'
		) );

		$status = $this->px2git->status();
		// var_dump($status);
		// var_dump($status['div']['sitemaps']);
		$this->assertEquals( $status['div']['sitemaps'][0]['file'], 'px-files/sitemaps/sitemap.csv' );
		$this->assertEquals( $status['div']['sitemaps'][0]['work_tree'], 'M' );
		$this->assertEquals( $status['div']['sitemaps'][1]['file'], 'px-files/sitemaps/sitemap.xlsx' );
		$this->assertEquals( $status['div']['sitemaps'][1]['work_tree'], 'M' );

		$this->px2git->commit_sitemaps('commit sitemaps - a');

		$log = $this->px2git->log();
		// var_dump($log);
		// var_dump(count($log));
		$this->assertNotEmpty( $log );
		$this->assertEquals( count($log), 2 );
		$this->assertEquals( $log[1]['title'], 'commit sitemaps - b' );

		// $this->git = new \PHPGit\Git();
		// $this->git->setRepository( $this->path_git_home );
		// var_dump($this->git->log());


		// --------------------------------------
		// コミットの詳細を取得する
		$hash = $log[1]['hash'];
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


	/**
	 * 後始末
	 */
	public function testDown(){

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testdata/htdocs/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testdata/htdocs/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testdata/htdocs/px-files/_sys/ram/caches/sitemaps/' ) );


		// ディレクトリを削除
		exec('rm -r '.__DIR__.'/testdata/git_home/.git/');
		$result = $this->fs->rmdir_r( $this->path_git_home );
		if(!$result){
			echo "FAILED to remove directory: ".$this->path_git_home."\n";
		}
		$this->assertFalse( $this->fs->is_dir( $this->path_git_home ) );
	}




	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	private function common_error( $output ){
		if( preg_match('/'.preg_quote('Fatal', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice', '/').'/si', $output) ){ return false; }
		return true;
	}


	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}
