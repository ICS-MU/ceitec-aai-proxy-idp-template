# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]
[Added]
- Added file phpcs.xml

## [v1.3.0]
[Added]
- Added support for pass selected IdP from SP in AuthnContextClassRef attribute.
    - It's required add this line into module_perun.php config file 
    <pre>
    'disco.removeAuthnContextClassRefPrefix' => 'urn:cesnet:proxyidp:',
    </pre> 

[Changed]
- Social Idps are not shown when adding institution

## [v1.2.0]
[Added]
- Possibility to show a warning in disco-tpl

[Changed]
- Whole module now uses a dictionary
- Updated Readme

[Removed]
- Removed function present_attributes($t, $attributes, $nameParent) from consentform.php

## [v1.1.1]
[Changed]
- Filling email is now required for reporting error
- Error reporting now uses dictionary
- Fixed the sentence below the list of all IdPs in addInstitutionApp

## [v1.1.0]
[Added]
- Short links for MU and VUT in disco-tpl.php

[Changed]
- If you go to add-institution app, now you see only the list of IdP without Social IdP, short links to some IdPs or link to add-institution app

## [v1.0.0]
[Added]
- Changelog

[Unreleased]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/master
[v1.3.0]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/v1.3.0
[v1.2.0]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/v1.2.0
[v1.1.1]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/v1.1.1
[v1.1.0]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/v1.1.0
[v1.0.0]: https://github.com/ICS-MU/ceitec-aai-proxy-idp-template/tree/v1.0.0
