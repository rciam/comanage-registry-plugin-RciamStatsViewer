# RciamStatsViewer
Rciam Statistics Viewer Plugin is used for presenting Statistics per **CO**llaboration in COmanage.
The plugin requires the [proxystatistics-simplesamlphp-module](https://github.com/CESNET/proxystatistics-simplesamlphp-module) by CESNET to be installed and active in the Proxy, since some of the presented statistics utilize its data Model. 

Specifically this plugin presents:
1. Summary of IdP/SP logins
   - Accessible by all Registered Users
2. Identity Providers details. Accessible by:
   - CO(U) administrators
   - Members of the privileged Group defined by the CO Administrator 
3. Service Providers details. Accessible by:
   - CO(U) administrators
   - Members of the privileged Group defined by the CO Administrator
4. Registered users. Accessible by:
   - CO administrators
   - Members of the privileged Group defined by the CO Administrator


## Installation

1. Run `git clone https://github.com/rciam/comanage-registry-plugin-RciamStatsViewer.git /path/to/comanage/local/Plugin/RciamStatsViewer`
2. Run `cd /path/to/comanage/app`
3. Run `Console/cake schema create --file schema.php --path /path/to/comanage/local/Plugin/RciamStatsViewer/Config/Schema`
4. 🍺

## Schema update
1. Run `Console/cake schema update --file schema.php --path /path/to/comanage/local/Plugin/RciamStatsViewer/Config/Schema`
   - During updates database alternations, which refer to constraints, have to be deployed manually 
 
## Configuration

After the installation, you have to configure the plugin before using it. 
1. Navigate to Configuration > Statistics Viewer
2. Specify the required information for the database configuration
3. Specify the required information for the statistics configuration. Table names must have the exact names as of yours in simpleSAMLphp module database.
4. (Optional) Specify a group where members of this group will have privileged access to the statistics (can see statistics like they were CO Administrator). 

## License

Licensed under the Apache 2.0 license, for details see [LICENSE](https://github.com/rciam/comanage-registry-plugin-RciamStatsViewer/blob/master/LICENSE).
