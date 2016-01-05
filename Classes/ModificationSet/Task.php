<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.ModificationSet.Svn
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

use CastToType;
use VersionControl_SVN;
use Xinc\Core\Build\BuildInterface;
use Xinc\Core\Exception\MalformedConfig;
use Xinc\Core\Plugin\ModificationSet\BaseTask;
use Xinc\Core\Plugin\ModificationSet\Result;

class Task extends BaseTask
{
    /**
     * Directory containing the Subversion project.
     *
     * @var string
     */
    private $strDirectory = 'working-copy';

    /**
     * Update repository if change detected.
     *
     * @var boolean
     */
    private $doUpdate = false;

    /**
     * The remote repository to clone from.
     *
     * @var string
     */
    private $strRepository = null;

    private $strUsername = null;

    private $strPassword = null;

    /**
     * Handles if trust-server-cert should be set or not.
     *
     * @var boolean
     */
    private $trustServerCert = false;

    protected function init(BuildInterface $build = null)
    {
		//
	}

    /**
     * Returns name of task.
     *
     * @return string Name of task.
     */
    public function getName()
    {
        return 'svn';
    }

    /**
     * Sets the svn checkout directory.
     *
     * @param string $strDirectory Directory for svn checkout
     */
    public function setDirectory($strDirectory)
    {
        $this->strDirectory = (string) $strDirectory;
    }

    /**
     * Gets the SVN checkout directory.
     *
     * @return string Directory which was set.
     */
    public function getDirectory()
    {
        return $this->strDirectory;
    }

    /**
     * Sets the remote repository.
     *
     * @param string $strRepository The remote repository.
     *
     * @return void
     */
    public function setRepository($strRepository)
    {
        $this->strRepository = (string) $strRepository;
    }

    /**
     * Gets the remote repository url.
     *
     * @return string Repository url which was set.
     */
    public function getRepository()
    {
        return $this->strRepository;
    }

    /**
     * Sets the username for the svn commands
     *
     * @param string $strUsername Username for svn.
     *
     * @return void
     */
    public function setUsername($strUsername)
    {
        $this->strUsername = (string) $strUsername;
    }

    /**
     * Gets the user name
     *
     * @return string Username which was set.
     */
    public function getUsername()
    {
        return $this->strUsername;
    }

    /**
     * Sets the password for the svn commands
     *
     * @param string $strPassword Password for svn
     *
     * @return void
     */
    public function setPassword($strPassword)
    {
        $this->strPassword = (string) $strPassword;
    }

    /**
     * Gets the password
     *
     * @return string Password which was set.
     */
    public function getPassword()
    {
        return $this->strPassword;
    }

    /**
     * Tells whether to update the working copy directly here or not
     *
     * @param string $strUpdate A string with a boolean representation.
     *
     * @return void
     */
    public function setUpdate($strUpdate)
    {
        $this->doUpdate = CastToType::_bool($strUpdate);
    }

    /**
     * Get if git should be automaticaly updated.
     *
     * @return boolean True if git repos should be updated.
     */
    public function doUpdate()
    {
        return $this->doUpdate;
    }

    /**
     * Sets if the svn should set the trust-server-cert flag.
     *
     * @param string $trustServerCert A string with a boolean representation.
     *
     * @return void
     */
    public function setTrustServerCert($trustServerCert)
    {
        $this->trustServerCert = $this->string2boolean($trustServerCert);
    }

    /**
     * Sets if the svn should set the trustServerCert flag.
     *
     * @return boolean True if trust-server-cert should be set otherwise false.
     */
    public function trustServerCert()
    {
        return $this->trustServerCert;
    }

    /**
     * Check if this modification set has been modified
     *
     * @param BuildInterface $build The running build.
     * @return Xinc::Core::Plugin::ModificationSet::Result The result of the check.
     */
    public function checkModified(BuildInterface $build)
    {
        $result = new Result();

        try {
            $this->svn = VersionControl_SVN::factory(
                array('info', 'log', 'status', 'update'), 
                array(
                    'fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ASSOC,
                    // @TODO VersionControl_SVN doesn't work as documented.
                    // 'path'      => $task->getDirectory(),
                    // 'url'       => $task->getRepository(),
                    'username' => $this->getUsername(),
                    'password' => $this->getPassword(),
                    'trustServerCert' => $this->trustServerCert(),
                )
            );

            $strRemoteVersion = $this->getRemoteVersion();
            $strLocalVersion = $this->getLocalVersion();
            
        } catch (\VersionControl_SVN_Exception $e) {
            $build->error('Test of Subversion failed: ' . $e->getMessage());
            $build->setStatus(BuildInterface::FAILED);
            $result->setStatus( Result::ERROR );
            return $result;
        }

        $result->setRemoteRevision($strRemoteVersion);
        $result->setLocalRevision($strLocalVersion);

        if ($strRemoteVersion !== $strLocalVersion) {
            try {
                $this->getModifiedFiles($result);
                $this->getChangeLog($result);
                if ($this->task->doUpdate()) {
                    $this->update($result);
                }
                $result->setStatus(
                    Xinc_Plugin_Repos_ModificationSet_AbstractTask::CHANGED
                );
            } catch (Exception $e) {
                var_dump($e->getMessage());
                $build->error('Processing SVN failed: ' . $e->getMessage());
                $result->setStatus(
                    Xinc_Plugin_Repos_ModificationSet_AbstractTask::FAILED
                );
            }
        }

        return $result;
    }

    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validate(&$msg = null)
    {
        $dir = $this->getDirectory();
        if (!isset($dir)) {
            $msg = 'Element modificationSet/svn - required attribute "directory" is not set.';
            return false;
        }

        return true;
    }
         
    /**
     * Gets remote version.
     *
     * @return string The remote version.
     */
    protected function getRemoteVersion()
    {
        return $this->getRevisionFromXML(
            $this->svn->info->run(
                array($this->getRepository())
            )
        );
    }

    /**
     * Gets local version.
     *
     * @return string The local version.
     */
    protected function getLocalVersion()
    {
        return $this->getRevisionFromXML(
            $this->svn->info->run(
                array($this->getDirectory())
            )
        );
    }

    /**
     * Returns the revison number from the PEAR::SVN Info XML
     *
     * @param array $arXml The XML as array from SVN info
     * @return string Revision number
     */
    protected function getRevisionFromXML(array $arXml)
    {
        if (isset($arXml['entry'][0]['commit']['revision'])) {
            // Latest commit in this directory path
            return $arXml['entry'][0]['commit']['revision'];
        }
        // Latest commit in the whole repository
        return $arXml['entry'][0]['revision'];
    }
}
