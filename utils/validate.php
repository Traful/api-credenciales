<?php
	class Validate {
		private $_passed = false,
				$_errors = array(),
				$_db = null,
				$_errnum = false;

		public function __construct($conn) {
			//$this->_db = DB::getInstance();
			$this->_db = $conn;
		}

		public function check($source, $items = array()) {
			unset($this->_errors);
			foreach($items as $item => $rules) {
				foreach($rules as $rule => $rule_value) {
					$value = trim($source[$item]);
					//$item = escape($item);
					$len = strlen($value);
					//if($rule === "required" && empty($value)) {
					//	Esta opción cuando es cero empty se hace true
					//if($rule === "required" && ($len == 0)) {
					//	Esta opción falla la validar si se paso un valor que puede ser o no de
					//	longitud 0
					if($rule === "required" && ($len === 0)) {
						if(isset($rules["msgerror"])) {
							$this->addError($rules["msgerror"]);
						} else {
							$this->addError($item . " es un valor requerido.");
						}
					//} else if(!empty($value)) {
					//} else if($len) {
					} else {
						switch($rule) {
							case "min":
								if(strlen($value) < $rule_value) {
									$this->addError($item . " debe tener al menos " . $rule_value . " caracteres.");
								}
								break;
							case "max":
								if(strlen($value) > $rule_value) {
									$this->addError($item . " debe tener como máximo " . $rule_value . " caracteres.");
								}
								break;
							case "matches":
								if($value != $source[$rule_value]) {
									$this->addError($rule_value . " debe ser igual a " . $item);
								}
								break;
							case "numeric":
								if(!is_numeric($value)) {
									$this->addError($item . " no es un número válido.");
									$this->_errnum = true;
								}
								break;
							case "mayorcero":
								if(is_numeric($value)) {
									if($value <= 0) {
										$this->addError($item . " debe ser mayor que cero.");	
									}
								} else {
									if(!$this->_errnum) {
										$this->addError($item . " no es un número válido.");
									}
								}
								break;
							case "unique":
								/*
								No funciona ver DBO
								$check = $this->_db->get($rule_value, array($item, "=", $value));
								if($check && $check->count()) {
									$this->addError($item . " ya existe.");
								}
								*/
								break;
						}
					}
				}
			}

			if(empty($this->_errors)) {
				$this->_passed = true;

			}
			return $this;

		}

		private function addError($string) {
			$this->_errors[] = $string;
		}

		public function errors() {
			if(isset($this->_errors)) {
				return $this->_errors;
			}
			return "";
		}
		
		public function passed() {
			return $this->_passed;
		}
	}
?>