<?php

use SimpleSAML\Module;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;
use SimpleSAML\Error\Exception;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\Module\perun\DiscoTemplate;

/**
 * This is simple example of template for perun Discovery service
 *
 * Allow type hinting in IDE
 * @var DiscoTemplate $this
 */

$this->data['jquery'] = array('core' => true, 'ui' => true, 'css' => true);

$this->data['head'] = '<link rel="stylesheet" media="screen" type="text/css" href="' .
    Module::getModuleUrl('discopower/assets/css/disco.css') . '" />';

$this->data['head'] .= '<link rel="stylesheet" media="screen" type="text/css" href="' .
    Module::getModuleUrl('ceitec/res/css/disco.css') . '" />';

$this->data['head'] .= '<script type="text/javascript" src="' .
    Module::getModuleUrl('discopower/assets/js/jquery.livesearch.js') . '"></script>';

$this->data['head'] .= '<script type="text/javascript" src="' .
    Module::getModuleUrl('discopower/assets/js/suggest.js') . '"></script>';

$this->data['head'] .= searchScript();

const WARNING_CONFIG_FILE_NAME = 'config-warning.php';
const WARNING_IS_ON = 'isOn';
const WARNING_USER_CAN_CONTINUE = 'userCanContinue';
const WARNING_TITLE = 'title';
const WARNING_TEXT = 'text';

const URN_CESNET_PROXYIDP_IDPENTITYID = "urn:cesnet:proxyidp:idpentityid:";

$authContextClassRef = null;
$idpEntityId = null;

$warningIsOn = false;
$warningUserCanContinue = null;
$warningTitle = null;
$warningText = null;
$config = null;

try {
    $config = Configuration::getConfig(WARNING_CONFIG_FILE_NAME);
} catch (Exception $ex) {
    Logger::warning("ceitec:disco-tpl: missing or invalid config-warning file");
}

if ($config != null) {
    try {
        $warningIsOn = $config->getBoolean(WARNING_IS_ON);
    } catch (Exception $ex) {
        Logger::warning("ceitec:disco-tpl: missing or invalid isOn parameter in config-warning file");
        $warningIsOn = false;
    }
}

if ($warningIsOn) {
    try {
        $warningUserCanContinue = $config->getBoolean(WARNING_USER_CAN_CONTINUE);
    } catch (Exception $ex) {
        Logger::warning(
            "ceitec:disco-tpl: missing or invalid userCanContinue parameter in config-warning file"
        );
        $warningUserCanContinue = true;
    }
    try {
        $warningTitle = $config->getString(WARNING_TITLE);
        $warningText = $config->getString(WARNING_TEXT);
        if (empty($warningTitle) || empty($warningText)) {
            throw new Exception();
        }
    } catch (Exception $ex) {
        Logger::warning("ceitec:disco-tpl: missing or invalid title or text in config-warning file");
        $warningIsOn = false;
    }
}

if (isset($this->data['AuthnContextClassRef'])) {
    $authContextClassRef = $this->data['AuthnContextClassRef'];
}

# Do not show social IdPs when using addInstitutionApp, show just header Add Institution
if ($this->isAddInstitutionApp()) {
    // Translate title in header
    $this->data['header'] = $this->t('{ceitec:ceitec:add_institution}');
    $this->includeAtTemplateBase('includes/header.php');
} else {
    if ($warningIsOn && !$warningUserCanContinue) {
        $this->data['header'] = $this->t('{ceitec:ceitec:warning}');
    }

    $this->includeAtTemplateBase('includes/header.php');

    if ($authContextClassRef != null) {
        foreach ($authContextClassRef as $value) {
            if (substr($value, 0, strlen(URN_CESNET_PROXYIDP_IDPENTITYID))
                === URN_CESNET_PROXYIDP_IDPENTITYID) {
                $idpEntityId = substr($value, strlen(URN_CESNET_PROXYIDP_IDPENTITYID), strlen($value));
                Logger::info("Redirecting to " . $idpEntityId);
                $url = $this->getContinueUrl($idpEntityId);
                HTTP::redirectTrustedURL($url);
                exit;
            }
        }
    }

    if ($warningIsOn) {
        if ($warningUserCanContinue) {
            echo '<div class="alert alert-warning">';
        } else {
            echo '<div class="alert alert-danger">';
        }
        echo '<h4> <strong>' . $warningTitle . '</strong> </h4>';
        echo $warningText;
        echo '</div>';
    }

    if (!$warningIsOn || $warningUserCanContinue) {
        if (!empty($this->getPreferredIdp())) {
            echo '<p class="descriptionp">' . $this->t('{ceitec:ceitec:disco_previous_selection}') . '</p>';
            echo '<div class="metalist list-group">';
            echo showEntry($this, $this->getPreferredIdp(), true);
            echo '</div>';


            echo getOr($this);
        }

        ?>
        <!--Preselected IdPs MU and VUT-->
        <div class="row text-center">
            <!--MU-->
            <div class="col-md-4 col-md-offset-2">
                <div class="metalist list-group">
                    <a class="btn btn-block social"
                       href="<?php echo $this->getContinueUrl('https://idp2.ics.muni.cz/idp/shibboleth'); ?>"
                       style="background: #002776">
                        <img src="data:image/png;base64,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">
                        <strong><?php echo $this->t('{ceitec:ceitec:sign_with}') ?> MU</strong>
                    </a>
                </div>
            </div>

            <!--VUT-->
            <div class="col-md-4">
                <div class="metalist list-group">
                    <a class="btn btn-block social"
                       href="<?php echo $this->getContinueUrl('https://www.vutbr.cz/SSO/saml2/idp'); ?>"
                       style="background: #e4002b">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAABA9pVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDUuNC4wIj4KICAgPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgICAgICAgICAgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iCiAgICAgICAgICAgIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIj4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOmluc3RhbmNlSUQ+eG1wLmlpZDo1N0M1QTU0QzA2MDgxMUU2QTBCN0Q1QjQ5MEREQjdCODwvc3RSZWY6aW5zdGFuY2VJRD4KICAgICAgICAgICAgPHN0UmVmOmRvY3VtZW50SUQ+eG1wLmRpZDo1N0M1QTU0RDA2MDgxMUU2QTBCN0Q1QjQ5MEREQjdCODwvc3RSZWY6ZG9jdW1lbnRJRD4KICAgICAgICAgPC94bXBNTTpEZXJpdmVkRnJvbT4KICAgICAgICAgPHhtcE1NOkRvY3VtZW50SUQ+eG1wLmRpZDo1N0M1QTU0RjA2MDgxMUU2QTBCN0Q1QjQ5MEREQjdCODwveG1wTU06RG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOkluc3RhbmNlSUQ+eG1wLmlpZDo1N0M1QTU0RTA2MDgxMUU2QTBCN0Q1QjQ5MEREQjdCODwveG1wTU06SW5zdGFuY2VJRD4KICAgICAgICAgPHhtcDpDcmVhdG9yVG9vbD5BZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3M8L3htcDpDcmVhdG9yVG9vbD4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cpg0DhgAAAJkSURBVFgJ7ZjPaxNBFMe/s7sxMdimxSL9H/JfeFbo/+EtePVmLwGhh+YPEHoq3jxUD948aItQxIt4ClhyMCVNspik+2P6JjEmJG83s53dksIOhJ3MvDfzme97s8ysuEC1JYFHWM/SdgiuQmwP15MPnkVgwZrCKaxAAa51caLoJDzqCqO6mXYLAgWm3awpEtB+sgtrowwZroYUlgU5GCFo/QEkZXWKhQFUQAKVeg2lvaeAp5GiBRve6Q9cPn8BORqQv50aIgM4Gdva3oRV2dCeSOxs0bqEtr2uYfQmCTSUm5/FT2g/7xtTj1RQFB/EuDFdBQdy6ELiL3UmDXHp3wZbzl8GUIVJYHjyGWGnB+n5DM1CE6ktikVUDl7RZlE5qBlqlRL0Gx5/wOjbOXktixIBCLiHR8ChWtHyqhbw6GXUR/nZHh6/byx2af33fzUJ8IxstQCnY06UnP6LfyYN6cJoMfnLKDh11gzT2HwSqqln4qcTvcAYwMTTRDoEzRZC16V+ftFhu0N9/Aslc0D3zVv09hu04doEwSsl4BA6f6DKFHD06SuuXr4eqyNQpmfywuuafBzWY/DuI7X7pM7tDxGZAsquyjuzKcy8Wd3mGm3z4c1HmOPJopoDmqqaK5graKqAqX+eg7mCpgqY+qeXg9xZNIV7coqAzFApAKZyYBUowTv/iW6tTp8+rsdRFY6D6y/fqb58U0sSdvEb1T45GH5hVfH16fo5+H/rUJdVi8BBx3mD0jbynk2scGwCmq2TS8mZvX6NSRx957uwvBeA/F3wLuRZPYftUK50KYOy+Xa2GmCVRfcGQ4CAiNUEXVUAAAAASUVORK5CYII=">
                        <strong><?php echo $this->t('{ceitec:ceitec:sign_with}') ?> VUT</strong>
                    </a>
                </div>
            </div>
        </div>
        <?php

        echo '<div class="row">';
        foreach ($this->getIdps('social') as $idpentry) {
            echo '<div class="col-md-4">';
            echo '<div class="metalist list-group">';
            echo showEntry($this, $idpentry, false);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo getOr($this);
        echo '<p class="descriptionp">';
        echo $this->t('{ceitec:ceitec:disco_institutional_account}');
        echo '</p>';
    }
}

if (!$warningIsOn || $warningUserCanContinue) {
    echo '<div class="inlinesearch">';
    echo '	<form id="idpselectform" action="?" method="get">
			<input class="inlinesearchf form-control input-lg" placeholder="' .
        $this->t('{ceitec:ceitec:name_of_institution}') . '" 
			
			type="text" value="" name="query" id="query" ' .
                'autofocus oninput="document.getElementById(\'list\').style.display=\'block\';"/>
		</form>';
    echo '</div>';

    echo '<div class="metalist list-group" id="list">';
    foreach ($this->getIdps() as $idpentry) {
        echo showEntry($this, $idpentry, false);
    }
    echo '</div>';

    echo '<br>';
    echo '<br>';

    echo '<div class="no-idp-found alert alert-info">';
    if ($this->isAddInstitutionApp()) {
        echo $this->t('{ceitec:ceitec:find_institution_contact}') .
            ' <a href="mailto:idm@ics.muni.cz?subject=Request%20for%20adding%20new%20IdP">idm@ics.muni.cz</a>';
    } else {
        echo $this->t('{ceitec:ceitec:find_institution_extended}') .
            ' <a class="btn btn-primary" href="https://login.ceitec.cz/add-institution/">' .
            $this->t('{ceitec:ceitec:add_institution_lower_case}') . '</a>';
    }
    echo '</div>';
}

$this->includeAtTemplateBase('includes/footer.php');

/*
 * Functions
 */
function searchScript()
{

    $script = '<script type="text/javascript">

	$(document).ready(function() { 
		$("#query").liveUpdate("#list");
	});
	
	</script>';

    return $script;
}

/**
 * @param DiscoTemplate $t
 * @param array $metadata
 * @param bool $favourite
 * @return string html
 */
function showEntry($t, $metadata, $favourite = false)
{

    if (isset($metadata['tags']) && in_array('social', $metadata['tags'])) {
        return showEntrySocial($t, $metadata);
    }

    $extra = ($favourite ? ' favourite' : '');
    $html = '<a class="metaentry' . $extra . ' list-group-item" href="' .
        $t->getContinueUrl($metadata['entityid']) . '">';

    $html .= '<strong>' . $t->getTranslatedEntityName($metadata) . '</strong>';

    $html .= showIcon($metadata);

    $html .= '</a>';

    return $html;
}

/**
 * @param DiscoTemplate $t
 * @param array $metadata
 * @return string html
 */
function showEntrySocial($t, $metadata)
{

    $bck = 'white';
    if (!empty($metadata['color'])) {
        $bck = $metadata['color'];
    }

    $html = '<a class="btn btn-block social" href="' . $t->getContinueUrl($metadata['entityid']) .
        '" style="background: ' . $bck . '">';

    $html .= '<img src="' . $metadata['icon'] . '">';

    $html .= '<strong>' . $t->t('{ceitec:ceitec:sign_with}') . $t->getTranslatedEntityName($metadata) . '</strong>';

    $html .= '</a>';

    return $html;
}

function showIcon($metadata)
{
    $html = '';
    // Logos are turned off, because they are loaded via URL from IdP. Some IdPs have bad configuration,
    // so it breaks the WAYF.

    /*if (isset($metadata['UIInfo']['Logo'][0]['url'])) {
        $html .= '<img src="' .
            htmlspecialchars(\SimpleSAML\Utils\HTTP::resolveURL($metadata['UIInfo']['Logo'][0]['url'])) .
            '" class="idp-logo">';
    } else if (isset($metadata['icon'])) {
        $html .= '<img src="' . htmlspecialchars(\SimpleSAML\Utils\HTTP::resolveURL($metadata['icon'])) .
            '" class="idp-logo">';
    }*/

    return $html;
}

function getOr($t)
{
    $or = '<div class="hrline">';
    $or .= '	<span>' . $t->t('{ceitec:ceitec:or}') . '</span>';
    $or .= '</div>';
    return $or;
}
