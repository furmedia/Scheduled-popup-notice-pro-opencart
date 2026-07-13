# Compatibility Matrix

This project ships separate adapter families because OpenCart 2, 3 and 4 have different controller, language, view, event and installer contracts.

| Family | Archive | Installer/runtime adapter | Certification |
|---|---|---|---|
| OpenCart 2.0-2.2 | `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | legacy OCMOD, PHP/TPL routes | release candidate |
| OpenCart 2.3 | `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | extension/module OCMOD, PHP/TPL routes | release candidate |
| OpenCart 3 | `scheduled_popup_notice_pro_oc3.ocmod.zip` | OCMOD, Twig, `user_token` routes | supported baseline |
| OpenCart 3 + Journal 3 | `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | OpenCart 3 OCMOD plus Journal footer fallback | release candidate |
| OpenCart 4.0 | `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | OpenCart 4 `install.json` extension layout | runtime certification required |
| OpenCart 4.1 | `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | OpenCart 4 `install.json` extension layout | runtime certification required |

## Required certification

Before publishing a compatibility claim, install the matching archive on the corresponding Foxly demo, test a clean install and uninstall, save settings, activate a current schedule, close and reopen the popup, verify blur cleanup, verify the order email hook, refresh modifications/cache, and test the default theme plus Journal where applicable.

The archives are intentionally disabled with an expired demo schedule. No package should be installed across generations. A custom theme or checkout may require a theme-specific hook because the popup is inserted at the storefront footer.
