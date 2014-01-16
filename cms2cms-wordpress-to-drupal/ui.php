<?php

function get_view_content()
{
    drupal_add_css(drupal_get_path('module', 'cms2cms') . '/css/cms2cms.css');
    drupal_add_js(drupal_get_path('module', 'cms2cms') . '/js/cms2cms.js');
    drupal_add_js(drupal_get_path('module', 'cms2cms') . '/js/jsonp.js');

    $dataProvider = new CmsPluginData();
    $viewProvider = new CmsPluginView();
    if (isset($_REQUEST['_wpnonce']) && !empty($_POST['cms2cms_logout'])) {

        $nonce = $_REQUEST['_wpnonce'];
        if ($viewProvider->verifyFormTempKey($nonce, 'cms2cms_logout')
            && $_POST['cms2cms_logout'] == 1
        ) {
            $dataProvider->clearOptions();
        }
    }

    $cms2cms_migrate_from = $viewProvider->getPluginSourceName();
    $cms2cms_migrate_to = $viewProvider->getPluginTargetName();

    $cms2cms_migrate_from_type = $viewProvider->getPluginSourceType();
    $cms2cms_migrate_to_type = $viewProvider->getPluginTargetType();

    $cms2cms_access_key = $dataProvider->getOption('cms2cms-key');
    $cms2cms_is_activated = $dataProvider->isActivated();

    $cms2cms_current_site_url = $dataProvider->getSiteUrl();
    $cms2cms_bridge_url = $dataProvider->getBridgeUrl();

    $cms2cms_authentication = $dataProvider->getAuthData();
    $cms2cms_download_bridge = $viewProvider->getDownLoadBridgeUrl($cms2cms_authentication);

    if(@$_GET['task'] == 'save-auth') {
        $dataProvider->saveOptions();
        print('{status:"Ok"}')  ;
        exit;
    }

    if(@$_GET['task'] == 'get-auth') {
        print json_encode($dataProvider->getOptions());
        exit;
    }


    $content = '<div class="wrap">

<div class="cms2cms-plugin">

    <div id="icon-plugins" class="icon32"><br></div>
    <h2>' . $viewProvider->getPluginNameLong() . '</h2>
    ';
    if ($cms2cms_is_activated) {
        $content .= '
        <div class="cms2cms-message">
                <span>
                    ' . sprintf(
                $viewProvider->__('You are logged in CMS2CMS as %s', 'cms2cms-migration'),
                $dataProvider->getOption('cms2cms-login')
            ) . '
                </span>
            <div class="cms2cms-logout">
                <form action="" method="post">
                    <input type="hidden" name="cms2cms_logout" value="1"/>
                    <input type="hidden" name="_wpnonce" value="' . $viewProvider->getFormTempKey('cms2cms_logout') . '"/>
                    <button class="button">
                        &times;
                        ' . $viewProvider->_e('Logout', 'cms2cms-migration') . '
                    </button>
                </form>
            </div>
        </div>';
    }
    $content .= '<ol id="cms2cms_accordeon">';

    $cms2cms_step_counter = 1;

    if (!$cms2cms_is_activated) {
        $content .= '
            <li id="cms2cms_accordeon_item_id_' . $cms2cms_step_counter++ . '" class="cms2cms_accordeon_item cms2cms_accordeon_item_register">
                <h3>
                    ' . $viewProvider->_e('Sign In', 'cms2cms-migration') . '
                    <span class="spinner"></span>
                </h3>
                <form action="' . $viewProvider->getRegisterUrl() . '"
                      callback="callback_auth"
                      validate="auth_check_password"
                      class="step_form"
                      id="cms2cms_form_register">

                    <h3 class="nav-tab-wrapper">
                        <a href="' . $viewProvider->getRegisterUrl() . '" class="nav-tab nav-tab-active" change_li_to=\'\'>
                            ' . $viewProvider->_e('Register CMS2CMS Account', 'cms2cms-migration') . '
                        </a>
                        <a href="' . $viewProvider->getLoginUrl() . '" class="nav-tab">
                            ' . $viewProvider->_e('Login', 'cms2cms-migration') . '
                        </a>
                        <a href="' . $viewProvider->getForgotPasswordUrl() . '" class="nav-tab cms2cms-real-link">
                            ' . $viewProvider->_e('Forgot password?', 'cms2cms-migration') . '
                        </a>
                    </h3>

                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="cms2cms-user-email">' . $viewProvider->_e('Email:', 'cms2cms-migration') . '</label>
                            </th>
                            <td>
                                <input type="text" id="cms2cms-user-email" name="email" value="' . $dataProvider->getUserEmail() . '" class="regular-text"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="cms2cms-user-password">' . $viewProvider->_e('Password:', 'cms2cms-migration') . '</label>
                            </th>
                            <td>
                                <input type="password" id="cms2cms-user-password" name="password" value="" class="regular-text"/>
                                <p class="description for__cms2cms_accordeon_item_register">
                                    ' . $viewProvider->_e('Minimum 6 characters', 'cms2cms-migration') . '
                                </p>
                                <input type="hidden" id="cms2cms-user-plugin" name="plugin" value="' . $viewProvider->getPluginReferrerId() . '" class="regular-text"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div>
                        <input type="hidden" id="cms2cms-site-url" name="siteUrl" value="' . $cms2cms_current_site_url . '"/>
                        <input type="hidden" id="cms2cms-bridge-url" name="sourceBridgePath" value="' . $cms2cms_bridge_url . '"/>
                        <input type="hidden" id="cms2cms-access-key" name="accessKey" value="' . $cms2cms_access_key . '"/>
                        <input type="hidden" name="termsOfService" value="1">
                        <input type="hidden" name="peioaj" value="">
                        <div class="error_message"></div>

                        <button type="submit" class="button button-primary button-large">
                            ' . $viewProvider->_e('Continue', 'cms2cms-migration') . '
                        </button>
                    </div>
                </form>
            </li>';
    }
    $content .= '

        <li id="cms2cms_accordeon_item_id_' . $cms2cms_step_counter++ . '" class="cms2cms_accordeon_item">
            <h3>
                ' . sprintf(
            $viewProvider->__('Connect %s', 'cms2cms-migration'),
            $cms2cms_migrate_from
        ) . '
                <span class="spinner"></span>
            </h3>
            <form action="' . $viewProvider->getVerifyUrl() . '"
                  callback="callback_verify"
                  validate="verify"
                  class="step_form"
                  id="cms2cms_form_verify">
                <ol>
                    <li>
                        <a href="' . $cms2cms_download_bridge . '" class="button">
                            ' . $viewProvider->__('Download the Bridge file', 'cms2cms-migration') . '
                        </a>
                    </li>
                    <li>
                        ' . $viewProvider->_e('Unzip it', 'cms2cms-migration') . '
                        <p class="description">
                            ' . $viewProvider->_e('Find the cms2cms.zip on your computer, right-click it and select Extract in the menu.', 'cms2cms-migration') . '
                        </p>
                    </li>
                    <li>
                        ' . sprintf(
            $viewProvider->__('Upload to the root folder on your %s website.', 'cms2cms-migration'),
            $cms2cms_migrate_from
        ) . '
                        <a href="' . $viewProvider->getVideoLink() . '" source="_blank">' . $viewProvider->_e('Watch the video', 'cms2cms-migration') . '</a>
                    </li>
                    <li>
                        ' . sprintf(
            $viewProvider->__('Specify %s website URL', 'cms2cms-migration'),
            $cms2cms_migrate_from
        ) . '
                        <br/>
                        <input type="text" name="sourceUrl" value="" class="regular-text" placeholder="' .
        sprintf(
            $viewProvider->__('http://your_%s_website.com/', 'cms2cms-migration'),
            strtolower($cms2cms_migrate_from_type)
        )
        . '"/>
                        <input type="hidden" name="sourceType" value="' . $cms2cms_migrate_from_type . '" />
                        <input type="hidden" name="targetUrl" value="' . $cms2cms_current_site_url . '" />
                        <input type="hidden" name="targetType" value="' . $cms2cms_migrate_to_type . '" />
                        <input type="hidden" name="sourceBridgePath" value="' . $cms2cms_bridge_url . '" />
                    </li>
                </ol>
                <div class="error_message"></div>
                <button type="submit" class="button button-primary button-large">
                    ' . $viewProvider->_e('Verify connection', 'cms2cms-migration') . '
                </button>
            </form>
        </li>

        <li id="cms2cms_accordeon_item_id_' . $cms2cms_step_counter++ . '" class="cms2cms_accordeon_item">
            <h3>
                ' . $viewProvider->_e('Configure and Start Migration', 'cms2cms-migration') . '
                <span class="spinner"></span>
            </h3>
            <form action="' . $viewProvider->getWizardUrl() . '"
                  class="cms2cms_step_migration_run step_form"
                  method="post"
                  id="cms2cms_form_run">
                ' . $viewProvider->_e("You'll be redirected to CMS2CMS application website in order to select your migration preferences and complete your migration.", 'cms2cms-migration') . '
                <input type="hidden" name="targetUrl" value="">
                <input type="hidden" name="targetType" value="">
                <input type="hidden" name="sourceUrl" value="">
                <input type="hidden" name="sourceType" value="">
                <input type="hidden" name="migrationHash" value="">
                <input type="hidden" name="sourceBridgePath" value="' . $cms2cms_bridge_url . '"/>
                <div class="error_message"></div>
                <button type="submit" class="button button-primary button-large">
                    ' . $viewProvider->_e('Start migration', 'cms2cms-migration') . '
                </button>
            </form>
        </li>
    </ol>

</div> <!-- /plugin -->

<div id="cms2cms-description">
    <p>
        ' .
        $viewProvider->_e(
            'CMS2CMS.com is the one-of-its kind tool for fast, accurate and trouble-free website migration from ' . $cms2cms_migrate_from . ' to ' . $cms2cms_migrate_to . '. Just a few mouse clicks - and your ' . $cms2cms_migrate_from . ' articles, categories, images, users, comments, internal links etc are safely delivered to the new ' . $cms2cms_migrate_to . ' website.',
            'cms2cms-migration'
        )
        . '
    </p>
    <p>
        <a href="http://www.cms2cms.com/how-it-works/" class="button" source="_blank">
            ' . $viewProvider->_e('See How it Works', 'cms2cms-migration') . '
        </a>
    </p>
    <p>
        ' .
        $viewProvider->_e('Take a quick demo tour to get the idea about how your migration will be handled.', 'cms2cms-migration')
        . '
    </p>
</div>

</div> <!-- /wrap -->';

    return $content;
}

