<?php
class CmsPluginData
{
    const CMS2CMS_OPTION_TABLE = 'cms2cms_options';

    public function getUserEmail()
    {
        global $user;

        return $user->mail;
    }

    public function getSiteUrl()
    {
        global $base_url;

        return $base_url;
    }

    public function getOption($name)
    {
        return variable_get($name);
    }

    public function setOption($name, $value)
    {
        variable_set($name, $value);
    }

    public function deleteOption($name)
    {
        variable_del($name);
    }

    public function getFrontUrl()
    {
        return $this->getSiteUrl() . '/' . $this->getBridgeUrl();
    }

    public function getBridgeUrl()
    {

        return '/' . drupal_get_path('module', 'cms2cms');
    }

    public function getAuthData()
    {
        $cms2cms_access_login = $this->getOption('cms2cms-login');
        $cms2cms_access_key = $this->getOption('cms2cms-key');

        return array(
            'email' => $cms2cms_access_login,
            'accessKey' => $cms2cms_access_key
        );
    }

    public function isActivated()
    {
        $cms2cms_access_key = $this->getOption('cms2cms-key');

        return ($cms2cms_access_key != false);
    }

    public function getOptions()
    {
        $key = $this->getOption('cms2cms-key');
        $login = $this->getOption('cms2cms-login');

        $response = 0;

        if ($key && $login) {
            $response = array(
                'email' => $login,
                'accessKey' => $key,
            );
        }

        return $response;
    }

    public function saveOptions()
    {
        $key = substr($_POST['accessKey'], 0, 64);
        $login = $_POST['login'];

        $cms2cms_site_url = $this->getSiteUrl();
        $bridge_depth = str_replace($cms2cms_site_url, '', $this->getFrontUrl());
        $bridge_depth = trim($bridge_depth, DIRECTORY_SEPARATOR);
        $bridge_depth = explode(DIRECTORY_SEPARATOR, $bridge_depth);
        $bridge_depth = count($bridge_depth);

        $response = array(
            'errors' => _('Provided credentials are not correct: ' . $key . ' = ' . $login)
        );

        if ($key && $login) {
            $this->deleteOption('cms2cms-key');
            $this->setOption('cms2cms-key', $key);

            $this->deleteOption('cms2cms-login');
            $this->setOption('cms2cms-login', $login);

            $this->deleteOption('cms2cms-depth');
            $this->setOption('cms2cms-depth', $bridge_depth);

            $response = array(
                'success' => true
            );
        }

        return $response;
    }

    public function clearOptions()
    {
        $this->deleteOption('cms2cms-login');
        $this->deleteOption('cms2cms-key');
        $this->deleteOption('cms2cms-depth');
    }

}