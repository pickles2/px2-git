<?php
/**
 * test for px2-git
 */
class cleanupTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $px2git;
	private $path_git_home;
	private $path_entry_script;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();

		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();

	}

	/**
	 * 後始末
	 */
	public function testCleanup(){

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testdata/htdocs/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertFalse( is_dir( __DIR__.'/testdata/htdocs/caches/p/' ) );
		$this->assertFalse( is_dir( __DIR__.'/testdata/htdocs/px-files/_sys/ram/caches/sitemaps/' ) );


		// ディレクトリを削除
		exec('rm -r '.__DIR__.'/testdata/git_home/.git/');
		$result = $this->fs->rmdir_r( __DIR__.'/testdata/git_home/' );
		if(!$result){
			echo "FAILED to remove directory: ".__DIR__.'/testdata/git_home/'."\n";
		}
		$this->assertFalse( $this->fs->is_dir( __DIR__.'/testdata/git_home/' ) );
	}

}
