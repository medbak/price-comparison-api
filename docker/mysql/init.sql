-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS price_comparison;
USE price_comparison;
CREATE DATABASE IF NOT EXISTS price_comparison_test;

-- Grant permissions to the app user
GRANT ALL PRIVILEGES ON price_comparison.* TO 'app'@'%';
GRANT ALL PRIVILEGES ON price_comparison_test.* TO 'app'@'%';
FLUSH PRIVILEGES;
