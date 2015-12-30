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

use Xinc\Core\Build\BuildInterface;
use Xinc\Core\Exception\MalformedConfig;
use Xinc\Core\Plugin\ModificationSet\BaseTask;

class Task extends BaseTask
{
    /**
     * Directory containing the Subversion project.
     *
     * @var string
     */
    private $strDirectory = '';

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
    private $strRepository = '';

    private $strUsername = null;

    private $strPassword = null;

    /**
     * Handles if trust-server-cert should be set or not.
     *
     * @var boolean
     */
    private $trustServerCert = false;


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
     * Sets the git checkout directory.
     *
     * @param string $strDirectory Directory for git checkout.
     *
     * @return void
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
        $this->doUpdate = $this->string2boolean($strUpdate);
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
     * @param Xinc_Build_Interface $build The running build.
     *
     * @return Xinc_Plugin_Repos_ModificationSet_Result The result of the check.
     */
    public function checkModified(BuildInterface $build)
    {
        return $this->plugin->checkModified($build, $this);
    }


    /**
     * Validates if a task can run by checking configs, directries and so on.
     *
     * @return boolean Is true if task can run.
     */
    public function validateTask()
    {
        if (!class_exists('VersionControl_SVN')) {
            throw new MalformedConfig(
                'PEAR::VersionControl_SVN doesn\'t exists. You need to install it to use this task.'
            );
        }
        if (!isset($this->strDirectory)) {
            throw new MalformedConfig(
                'Element modificationSet/svn - required attribute "directory" is not set.'
            );
        }

        $file = $this->strDirectory;
        $file2 = Xinc::getInstance()->getWorkingDir() . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($file) && !file_exists($file2)) {
            Xinc_Logger::getInstance()->error(
                'Directory ' . $file2 . ' does not exist.'
            );
            return false;
        } elseif (file_exists($file2)) {
            $this->strDirectory = $file2;
        }
        return true;
    }
}
