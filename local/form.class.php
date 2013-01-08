<?php

/**
 * Form class
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version 0.01a
 * @since 07:25 2012/2/22
 */
defined('SYS_ROOT') || die('Access denied');

class form {

	/**
	 * Show form list
	 *
	 * @access public
	 * @param integer $start
	 * @param integer $offset
	 * @param return html
	 */
	public function showForms($keyword = '', $start = 0, $offset = 10) {
		// parse params
		$keyword = trim($keyword);
		$start = max(0, (int) $start);
		$offset = max(0, (int) $offset);
		$total = $list = NULL;
		// get list from database
		$sql = "SELECT * FROM @__form ";
		if ($keyword)
			$sql .= " WHERE form_name LIKE '%:keyword%' ORDER BY sort DESC";
		// get number
		$total = db()->field(str_replace('*', 'COUNT(1) AS n', $sql), array('keyword' => $keyword));
		if ($offset) {
			$sql .= " LIMIT $start, $offset";
			// generate page html
			$url_tpl = '<a href="%d">%d</a>';
			$page = page($url_tpl, $total, $offset, $start, 4);
		}
		$list = db()->rows($sql, array('keyword' => $keyword));
		// compile with template
		$html = '';
		ob_start();
		require SYS_ROOT . 'templates/form_list.php';
		$html = ob_get_contents();
		ob_end_clean();
		// return html
		return $html;
	}

	/**
	 * Show form
	 *
	 * @access public
	 * @param string $form_id
	 * @return html
	 */
	public function showForm($form_id) {
		// parse form id
		$form_id = (int)$form_id;
		if ($form_id < 1)
			return '';
		// get form detail from database
		$sql = "SELECT * FROM @__form_element WHERE form_id = :form_id ORDER BY sort, elm_id";
		$form_elements = db()->row($sql, array('form_id'=>$form_id));
		// compile with template
		$html = '';
		ob_start();
		require SYS_ROOT. 'templates/form_detail.php';
		$html = ob_get_contents();
		ob_end_clean();
		// return html
		return $html;
	}

	/**
	 * Create form page
	 *
	 * @access public
	 * @return html
	 */
	public function showCreateForm() {
		$html = '';
		ob_start();
		require SYS_ROOT. 'templates/form_detail.php';
		$html = ob_get_clean();
		ob_end_clean();
		return $html;
	}

	/**
	 * Create form process
	 *
	 * @access public
	 * @param array $form_data
	 * @return boolean
	 */
	public function doCreateForm($form_data) {
		// @todo save data to database
		// @todo return boolean
	}

	/**
	 * Update form page
	 *
	 * @access public
	 * @param string $form_id
	 * @return html
	 */
	public function showUpdateForm($form_id) {
		// @todo get form data from database
		// @todo get compile with template
		// @todo return html
	}

	/**
	 * Update form process
	 *
	 * @access public
	 * @param string $form_id
	 * @param array $form_data
	 * @return boolean
	 */
	public function doUpdateForm($form_id, $form_data) {
		// @todo udpate database
		// @todo return boolean
	}

	/**
	 * Delete form process
	 *
	 * @access public
	 * @param string $form_id
	 * @return boolean
	 */
	public function doDeleteForm($form_id) {
		// @todo delete from database
		// @todo return boolean
	}

	/**
	 * Output
	 *
	 * @access public
	 * @param string $form_name
	 * @param array $form_data
	 * @return html
	 */
	public function output($form_name, $form_data) {
		// @todo get form data
		// @todo get template
		// @todo compile
		// @todo return html
	}

	/**
	 * Input
	 *
	 * @access public
	 * @param string $form_name
	 * @param array $form_data
	 * @return array
	 */
	public function input($form_name, $form_data) {
		// @todo get form data
		// @todo filter data by form data
		// @todo return data
	}

}