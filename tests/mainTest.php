<?php
/**
 * test for px2-git
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $path_git_home;
	private $path_entry_script;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();

		$this->path_git_home = __DIR__.'/testdata/';
		$this->path_entry_script = __DIR__.'/testdata/htdocs/.px_execute.php';
	}


	/**
	 * $px2git provider
	 * @return array $px2git list
	 */
	public function px2gitProvider(){
		$this->setup();
		// var_dump($this->path_entry_script);
		// var_dump($this->path_git_home);
		// var_dump(__LINE__);

		$git = new \PHPGit\Git();
		$res = $git->init($this->path_git_home, array());

		$rtn = array();

		// --------------------------------
		// entry_script から生成
		array_push($rtn, array(new tomk79\pickles2\git\main( $this->path_entry_script )));

		// --------------------------------
		// $px のインスタンスから生成
		$memo_SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
		$_SERVER['SCRIPT_FILENAME'] = $this->path_entry_script;
		$cd = realpath('.');
		chdir(__DIR__.'/testdata/htdocs/');
		$px = new picklesFramework2\px('./px-files/');

		$px2git = new tomk79\pickles2\git\main( $px );
		array_push($rtn, array($px2git));

		$_SERVER['SCRIPT_FILENAME'] = $memo_SCRIPT_FILENAME;
		chdir($cd);

		return $rtn;
	}

	/**
	 * git init
	 * @dataProvider px2gitProvider
	 */
	public function testInit($px2git){

		$px2git->init( $this->path_git_home );
		$this->assertTrue( $this->fs->is_dir( $this->path_git_home.'.git' ) );

		// 後始末
		$this->fs->rmdir_r( $this->path_git_home.'.git' );
		$this->assertFalse( $this->fs->is_dir( $this->path_git_home.'.git' ) );
	}

	/**
	 * git log
	 * @dataProvider px2gitProvider
	 */
	public function testCommitLog($px2git){

		$px2git->init( $this->path_git_home );
		$this->assertTrue( $this->fs->is_dir( $this->path_git_home.'.git' ) );

		// $px2git->commit_sitemap();
		//
		// $log = $px2git->log();
		// var_dump($log);

		// 後始末
		$this->fs->rmdir_r( $this->path_git_home.'.git' );
		$this->assertFalse( $this->fs->is_dir( $this->path_git_home.'.git' ) );
	}

	/**
	 * 後始末
	 */
	public function testAfter(){

		// -------------------
		// execute test
		$output = $this->passthru( [
			'php',
			__DIR__.'/testdata/htdocs/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testdata/htdocs/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testdata/htdocs/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testdata/htdocs/px-files/_sys/ram/caches/sitemaps/' ) );


		$this->fs->rmdir_r( $this->path_git_home.'.git' );
		$this->assertFalse( $this->fs->is_dir( $this->path_git_home.'.git' ) );
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
