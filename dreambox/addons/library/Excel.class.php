<?php
require_once ADDON_PATH . '/library/excel/PHPExcel.php';
/*
 * Excel导入/导出工具类
 */
class Excel {
	/**
	 * 导入Excel
	 *
	 * @param unknown $file
	 *        	要导入的文件路径
	 * @return boolean PHPExcel
	 *         如果解析失败，返回false；否则返回解析后的PHPExcel
	 */
	static function read($file) {
		// 设置缓存方式，避免出来大文件数据时内存移除风险
		$reader = new PHPExcel_Reader_Excel2007 ();
		// 若不是Excel2007格式，改用低版本格式解析
		if (! $reader->canRead ( $file )) {
			$reader = new PHPExcel_Reader_Excel5 ();
			// 若低版本格式也不能解析，返回false
			if (! $reader->canRead ( $file )) {
				return false;
			}
		}
		return $reader->load ( $file );
	}
	/**
	 * 通过上传导入Excel，文件统一先上传到$_SITE/data/excel目录，再解析
	 *
	 * @param array $data
	 *        	上传的数据文件，通过$_FILE['filename']获取
	 * @param unknown $key
	 *        	用于存储时给文件命名的标示符种子，唯一，推荐使用mid。
	 * @return boolean PHPExcel
	 *         如果解析失败，返回false；否则返回解析后的PHPExcel
	 */
	static function readForUpload(array $data, $key) {
		if (! is_array ( $data )) {
			return false;
		}
		if ($data ['error'] == 0) {
			$max_size = 2000000;
			$name = 'excel' . time () . $key . mt_rand ( 10000, 99999 ) . strrchr ( $data ['name'], '.' );
			$dir = DATA_PATH . '/excel/';
			if (! is_dir ( $dir )) {
				mkdir ( $dir, 0777, true );
			}
			if (! move_uploaded_file ( $data ['tmp_name'], $dir . $name )) {
				return false;
			}
		} else {
			return false;
		}
		return Excel::read ( DATA_PATH . '/excel/' . $name );
	}
	/**
	 * Excel导出下载
	 *
	 * @param array $data
	 *        	要导出的数据，由数据库查询返回。
	 * @param array $top
	 *        	表头数据和列信息，格式为array(column=>title)
	 * @param string $fileName
	 *        	要导出的文件名
	 * @param string $type
	 *        	导出文件类型，支持Excel2007，默认为Excel5
	 */
	public function export(array $data, array $top, $fileName = 'export', $type = 'Excel5') {
		// 设置缓存方式，避免出来大文件数据时内存移除风险
		PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
		$objPHPExcel = new PHPExcel ();
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 这里是表，即sheet，一个excel文件可以有很多个表（sheet）
		$sheet = $objPHPExcel->getActiveSheet ( 0 );
		$sheet->setTitle ( $fileName ); // sheet的标题
		$columnLength = count ( $top );
		// 设置表头
		for($i = 0; $i < $columnLength; $i ++) {
			$title = current ( array_slice ( $top, $i, 1 ) );
			$sheet->setCellValueByColumnAndRow ( $i, 1, $title );
		}
		
		// 设置内容
		$row = 2;
		foreach ( $data as $v ) {
			$col = 0;
			foreach ( $top as $column => $title ) {
				$sheet->setCellValueByColumnAndRow ( $col, $row, $v [$column] );
				$col ++;
			}
			$row ++;
		}
		// 输出
		header ( 'Content-Type: application/vnd.ms-excel;charset=GB2312' );
		header ( 'Content-Disposition: attachment;filename=' . iconv ( 'UTF-8', 'GB2312', $fileName ) . ($type == 'Excel2007' ? '.xlsx' : '.xls') );
		// header('Content-Disposition: attachment; filename*="utf8\'\'' . $file_name . '"');
		header ( 'Cache-Control: max-age=0' );
		$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, $type );
		$objWriter->save ( 'php://output' ); // 这里生成excel后会弹出下载
	}
	/**
	 * 导出csv格式
	 * @param array $data
	 * @param array $top
	 * @param string $fileName
	 */
	public function exportCsv(array $data, array $top, $fileName = 'export') {
		header ( 'Content-Type: application/vnd.ms-excel' );
		
		header ( 'Content-Disposition: attachment;filename="' .  iconv ( 'UTF-8', 'gbk', $fileName )  . '.csv"' );
		
		header ( 'Cache-Control: max-age=0' );
		$head = array ();
		foreach ( $top as $i => $v ) {
				
			// CSV的Excel支持GBK编码，一定要转换，否则乱码
				
			$head [$i] = iconv ( 'utf-8', 'gbk', $v );
		}
		$fp = fopen ( 'php://output', 'a' );
		// 将数据通过fputcsv写到文件句柄
		fputcsv ( $fp, $head );
		
		foreach ( $data as $value ) {
			$out_value=array();
			foreach ($head as $k=>$v){
				$out_value[]=iconv ( 'utf-8', 'gbk', "\t" . $value[$k] );;
			}				
			fputcsv ( $fp, $out_value );
		}
		fclose($fp);
	}
	/**
	 * 设置列表样式单元格
	 * @param PHPExcel $objPHPExcel
	 * @param string $cell
	 */
	public function setList(PHPExcel $objPHPExcel,$cell='A1',$lists='列表项1,列表项2',$config=array()){
		$objActSheet = $objPHPExcel->getActiveSheet();
		!is_array($lists)&&$lists=explode(',', $lists);
		
		$objValidation = $objActSheet->getCell($cell)->getDataValidation();
		$objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)
					-> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
					-> setAllowBlank(isset($config['allowBlank'])?$config['allowBlank']:false)
					-> setShowInputMessage(true)
					-> setShowErrorMessage(true)
					-> setShowDropDown(true)
					-> setFormula1('"'.join(',', $lists).'"');
		!$config['allowBlank']&&$objActSheet->setCellValue($cell,$lists[0]);
		if($config['error']){
			$objValidation-> setError($config['error']);
		}
		if($config['errorTitle']){
			$objValidation-> setErrorTitle($config['errorTitle']);
		}
		if($config['promptTitle']){
			$objValidation-> setPromptTitle($config['promptTitle']);
		}
	}
	public function out($path) {
		// 创建一个excel
		$objPHPExcel = new PHPExcel ();
		// 设置excel的属性：
		// 创建人
		$objPHPExcel->getProperties ()->setCreator ( "Maarten Balliauw" );
		// 最后修改人
		$objPHPExcel->getProperties ()->setLastModifiedBy ( "Maarten Balliauw" );
		// 标题
		$objPHPExcel->getProperties ()->setTitle ( "Office 2007 XLSX Test Document" );
		// 题目
		$objPHPExcel->getProperties ()->setSubject ( "Office 2007 XLSX Test Document" );
		// 描述
		$objPHPExcel->getProperties ()->setDescription ( "Test document for Office 2007 XLSX, generated using PHP classes." );
		// 关键字
		$objPHPExcel->getProperties ()->setKeywords ( "office 2007 openxml php" );
		// 种类
		$objPHPExcel->getProperties ()->setCategory ( "Test result file" );
		// 设置当前的sheet
		$objPHPExcel->setActiveSheetIndex ( 0 );
		// 设置sheet的name
		$objPHPExcel->getActiveSheet ()->setTitle ( '第一个sheet' );
		
		// 直接输出到网页
		$objWriter = new PHPExcel_Writer_Excel2007 ( $objPHPExcel );
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control:must-revalidate, post-check=0, pre-check=0" );
		header ( "Content-Type:application/force-download" );
		header ( "Content-Type:application/vnd.ms-execl" );
		header ( "Content-Type:application/octet-stream" );
		header ( "Content-Type:application/download" );
		;
		header ( 'Content-Disposition:attachment;filename="resume.xls"' );
		header ( "Content-Transfer-Encoding:binary" );
		$objWriter->save ( 'php://output' );
	}
	
	/**
	 * 设置样式
	 *
	 * @param PHPExcel $objPHPExcel        	
	 */
	public function setStyle(PHPExcel $objPHPExcel) {
	}
}