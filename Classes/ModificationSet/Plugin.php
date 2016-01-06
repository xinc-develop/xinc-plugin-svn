<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet
 * @author    Arno Schneider <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */
 
namespace Xinc\Plugin\Svn\ModificationSet;

use Xinc\Core\Plugin\Base;

class Plugin extends Base
{
    /**
     * @var VersionControl_SVN The svn object.
     */
    private $svn = null;

    /**
     * @var Xinc_Plugin_Repos_ModificationSet_Svn_Task The task config.
     */
    private $task = null;
    
    public function getName()
    {
		return 'ModificationSet/SVN';
	}

    /**
     * Returns definition of task.
     *
     * @return array Array of definition.
     */
    public function getTaskDefinitions()
    {
        return array(new Task($this));
    }

    /**
     * Updates local svn to the remoteRevision for this test.
     *
     * @param Xinc_Plugin_Repos_ModificationSet_Result $result The Result to get
     *  Hash ids from and set modified files.
     *
     * @return void
     * @throw Xinc_Exception_ModificationSet
     */
    protected function update(
        Xinc_Plugin_Repos_ModificationSet_Result $result
    ) {
        $arUpdate = $this->svn->update->run(
            array($this->task->getDirectory()),
            array('r' => $result->getRemoteRevision())
        );

        if (false === $arUpdate) {
            throw new Xinc_Exception_ModificationSet(
                'SVN update local working copy failed',
                0
            );
        }
    }

    /**
     * Validate if the plugin can run properly on this system
     *
     * @return boolean True if plugin can run properly otherwise false.
     */
    public function validate(&$msg = null)
    {
		if(!class_exists('VersionControl_SVN',true)) {
			$msg = 'VersionControl_SVN not installed.';
            return false;
        }
        return true;
    }
}
