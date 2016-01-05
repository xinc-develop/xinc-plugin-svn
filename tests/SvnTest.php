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

use Xinc\Plugin\Svn\ModificationSet\Plugin;
use Xinc\Plugin\Svn\ModificationSet\Task as Svn;
use Xinc\Core\Test\BaseTest;

/**
 * @test Test class for Xinc::Plugin::Svn::ModificationSet::Task
 */
class TestSvnTask extends BaseTest
{  
	public function testSvnConstruct()
	{
	    $svn = new Svn(new Plugin());
	    $this->assertInstanceOf('Xinc\Plugin\Svn\ModificationSet\Task',$svn);
	}
	
	public function testSvnValidate()
	{
	    $svn = new Svn(new Plugin());
	    $this->assertTrue($svn->validate());
	}
	
	public function testSvnDefaultParameter()
	{
	    $svn = new Svn(new Plugin());
	    
	    $this->assertEquals('working-copy',$svn->getDirectory());
	    $this->assertNull($svn->getUsername(),'username');
	    $this->assertNUll($svn->getPassword(),'password');
	    $this->assertNull($svn->getRepository(),'repository');
	    $this->assertFalse($svn->doUpdate(),'do-update');
	    $this->assertFalse($svn->trustServerCert(),'server-certificate');
	}
}
