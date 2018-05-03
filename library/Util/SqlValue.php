<?php
/**
 * Should be implemented by classes that convert to SQL values.
 */
interface Util_SqlValue {
	/**
	 * Convert to the SQL value.
	 * No need to quote the string, addslashes, etc.
	 * @return int | float | string | null The SQL (scalar) value.
	 */
	public function get_as_sql_value();
}
?>
