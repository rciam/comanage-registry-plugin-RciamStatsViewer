# RciamStatsViewer
Rciam Statistics Viewer Plugin for generating Statistics per CO.

Specifically this plugin presents:
1. Number of registered users (monthly), 
2. Number of connected SPs (monthly),
3. Number of connected IdPs (monthly), 
4. Number of Communities - internal VOs, i.e. managed in Check-in COmanage (monthly)
5. Number of Communities - connected Community AAI services (IdPs) (monthly)
6. Number of Infrastructure Proxy services (SPs) (monthly)
7. Number of IdP/SP logins (daily,  daily average per month)

The information will be accessible to the ops team (CO admins). VO managers (COU admins) should be able to view stats specific to their VO

## Installation

1. Run `git clone <url>` to the folder <path_to_comanage>/local/Plugins
2. Run `Console/cake schema create --file schema.php --path <path_to_comanage>/local/Plugin/RciamStatsViewer/Config/Schema` inside folder <path_to_comanage>/local/Plugins/RciamStatsViewer
3. Run `psql -h host -U username -d databaseName -a -f <path_to_comanage>/local/Plugin/RciamStatsViewer/Config/Schema/constraints.sql`

## Configuration

After installation, you have to configure the plugin before using it. 
1. Navigate to Configuration > Statistics Viewer
2. Specify the required information for the database configuration
3. Specify the required information for the statistics configuration. Table names must have the exact names as of yours in simpleSAMLphp module database.

## License

Licensed under the Apache 2.0 license, for details see `LICENSE`.
