<?php
/**
 * @version 3.0
 * @author Arno Schneider
 * @author Sebastian Knapp
 * @copyright 2007 Arno Schneider, Barcelona
 * @copyright 2015 Xinc Development Team, https://github.com/xinc-develop/
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

use Xinc\Core\Build\BuildInterface;
use Xinc\Core\Config\Config;
use Xinc\Core\Project\Project;
use Xinc\Core\Project\Status as ProjectStatus;
use Xinc\Core\Task\Slot;
use Xinc\Core\Test\BaseTest;
/**
 * @test Test class for Xinc::Core::Project::Project
 */
class TestModificationSet extends BaseTest
{  
	public function getSvnRoot()
	{
		exec('svn info --xml',$xml);
		$xml = simplexml_load_string(join('',$xml));
		$root = $xml->xpath('/info/entry/repository/root');
		
		return "{$root[0]}";
	}
		
	public function getSvnVersion()
	{
		exec("svn --version --quiet",$out);
		return $out[0];
	}
	
	public function relocateRepo($wc,$repo)
	{
		$current = getcwd();
		chdir($wc);
		$this->getSvnRoot();
	    if(version_compare('1.7.0',$this->getSvnVersion(),'<=')) {
			$old = $this->getSvnRoot();
			exec("svn switch --relocate $old $repo");	
		}
		else {
			exec("svn relocate $repo");
		}
		chdir($current);
	}
	
	public function setUp()
	{
	    $one = __DIR__ . '/working-copy/one';
	    $repo = __DIR__ . '/repos/one';
	    
	    $this->relocateRepo($one,$repo);
	}
	
	public function testProjectFromConfig1()
	{
		$conf = $this->defaultConfig();
	    $conf->setOption('config-file', __DIR__ . '/config/plugins.xml');
	    $conf->setOption('project-file', __DIR__ . '/config/project-svn.xml');
	    
	    $this->projectXml($conf,$reg)->load($conf,$reg);
	    $build2 = $this->aBuildWithConfig($conf);
	    $project = $reg->getProject("ProjectSvn");
	    $this->assertInstanceOf('Xinc\Core\Project\Project',$project);
	    $build2->process(Slot::PRE_PROCESS);
	    
	    $this->assertEquals($build2->getStatus(),BuildInterface::PASSED);
	    
	    return;
	    $iterator = $reg->getProjectIterator();
	    $this->assertInstanceOf('ArrayIterator',$iterator);
	    $project2 = $iterator->current();
	    $this->assertSame($project,$project2);
	    $engine = $reg->getEngine($project->getEngineName());
	    $this->assertInstanceOf('Xinc\Core\Engine\EngineInterface',$engine);
	 }
}
