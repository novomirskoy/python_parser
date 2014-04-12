<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);

class AddParsingAdvertsCommand extends CConsoleCommand
{
	public $steering_wheel_array = [
		'2' => 'левый',
		'1' => 'правый',
	];
	
	public $body_array = [
		'1'		=> 'седан',
		'2'		=> 'универсал',
		'3'		=> 'пикап',
		'4'		=> 'хэтчбек',
		'5'		=> 'кабриолет',
		'6'		=> 'купе',
		'7'		=> 'фургон',
		'8'		=> 'внедорожник',
		'9'		=> 'лимузин',
		'10'	=> 'минивэн',
		'12'	=> 'автобус',
	];
	
	public $transmission_array = [
		'1' => 'передний',
		'2' => 'задний',
		'3' => 'полный',
		'4' => 'полный подключаемый',
	];
	
	public $engine_type_array = [
		'1' => 'бензиновый',
		'2' => 'дизельный',
		'3' => 'бензиновый турбо',
		'4' => 'турбодизель',
		'5' => 'гибридный',
	];
	
	public $kpp_array = [
		'1' => 'механическая',
		'2' => 'автоматическая',
		'3' => 'вариатор',
		'5' => 'роботизированная',
	];
	
	public $state_array = [
		'1' => 'отличное',
		'2' => 'хорошее',
		'3' => 'среднее',
		'4' => 'битый',
	];
	
	public $custom_house_state = [
		'1' => 'растаможен',
		'2' => 'нерастаможен',
	];
	
	public $required_params = [
		"brand",
		"model",
		"year",
		"seller",
		"photo",
		"body",
		"transmission",
		"steering_wheel",
		"engine_type",
		"kpp",
		"engine_volume",
		"engine_power",
		"color",
		"phone",
		"price",
		"distance",
		"text",
		"region",
	];
	
	public static $brand_array = [];
	
	public static $model_array = [];
	
	public function brand_array()
	{
		$brand_array = Yii::app()
				->db
				->createCommand("SELECT id, name FROM brand_new_car WHERE hidden=0 ORDER BY name")
				->queryAll();
		return $brand_array;
	}

	public function model_array()
	{
		$model_array = Yii::app()
			->db
			->createCommand("SELECT id, name, brand FROM model_new_car WHERE hidden=0 ORDER BY name")
			->queryAll();
		return $model_array;
	}
	
	public function check_advert($advert)
	{
		foreach ($this->required_params as $required_param)
		{
			if (empty($advert[$required_param]) && ($advert[$required_param] != " "))
			{
//				echo $required_param, "\n";
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Форматирует параметр "distance"
	 * @param type $distance
	 * @return type string
	 */
	public function format_distance($distance)
	{
		$distance = str_replace(" ", "", $distance);
		preg_match_all('/[\d]+/', $distance, $matches);
		$distance = implode("", $matches[0]);
		
		return $distance;
	}
	
	public function format_price($price)
	{
		$price = str_replace(" ", "", $price);

		return $price;
	}
	
	/**
	 * Форматирует параметр "phone"
	 * @param type $phone
	 * @return string
	 */
	public function format_phone($phone)
	{
		$phone = str_replace(" ", "", $phone);
		$phone = str_replace("Б", "6", $phone);
		
		if ((strlen($phone) > 0) && ($phone[0] == "("))
			$phone = "8" . $phone;
		
		return $phone;
	}
	
	public function format_region($region)
	{
		$region = preg_replace("/\s\s+/", "", $region);
		
		return $region;
	}
	
	public function format_seller($seller)
	{
		$seller = preg_replace("/\s\s+/", "", $seller);
		$seller = str_replace("\n", "", $seller);
		
		return $seller;
	}
	
	public function format_color($color)
	{
//		$color = preg_replace("/\s\s+/iu", "", $color);
		$color = str_replace(" ", "", $color);
		
		return $color;
	}
	
	public function format_transmission($transmission)
	{
		foreach ($this->transmission_array as $key => $item)
		{
			$find = preg_match("/\b{$transmission}\b/iu", $item);

			if ($find)
				return $key;
		}
	}
	
	public function format_engine_type($engine_type)
	{
		switch ($engine_type)
		{
			case "Бензин":
				$engine_type = 1;
				break;
			case "Дизель":
				$engine_type = 2;
				break;
			case "Гибрид":
				$engine_type = 5;
				break;
			default:
				$engine_type = "";
				break;
		}
		
		return $engine_type;
	}
	
	public function format_kpp($kpp)
	{
		switch ($kpp)
		{
			case "Автомат":
				$kpp = 2;
				break;
			case "Механика":
				$kpp = 1;
				break;
			case "Робот":
				$kpp = 5;
				break;
			case "Вариатор":
				$kpp = 3;
				break;
			default:
				$kpp = "";
				break;
		}
		
		return $kpp;
	}
	
	public function format_state($state)
	{
		switch ($state)
		{
			case "Отличное состояние":
				$state = 1;
				break;
			case "Хорошее состояние":
				$state = 2;
				break;
			case "Среднее состояние":
				$state = 3;
				break;
			case "Битый":
				$state = 4;
				break;
			case "Новый":
				$state = 1;
				break;
			default:
				$state = "";
				break;
		}
		
		return $state;
	}
	
	public function format_body($body)
	{
		$pattern = "/\b(седан|универсал|пикап|хетчбэк|кабриолет|купе|фургон|внедорожник|лимузин|минивэн|автобус|кроссовер|микроавтобус|лифтбэк|фастбэк)\b/iu";
		preg_match($pattern, $body, $matches);
		
		
		if (isset($matches[0]))
		{
			$find = trim(strtolower($matches[0]));
			
//			echo "Найдено ", $find, " в ", $body, "\n";
			
			switch (strtolower($find))
			{
				case "седан":
				case "Седан":
					$body = 1;
					break;
				case "Универсал":
				case "универсал":
					$body = 2;
					break;
				case "пикап":
					$body = 3;
					break;
				case "Хетчбэк":
				case "хэтчбэк":
				case "хетчбэк":
					$body = 4;
					break;
				case "Кабриолет":
				case "кабриолет":
					$body = 5;
					break;
				case "Купе":
				case "купе":
					$body = 6;
					break;
				case "Фургон":
				case "фургон":
					$body = 7;
					break;
				case "Внедорожник":
				case "внедорожник":
					$body = 8;
					break;
				case "Лимузин":
				case "лимузин":
					$body = 9;
					break;
				case "Минивэн":
				case "минивэн":
					$body = 10;
					break;
				case "Автобус":
				case "автобус":
					$body = 12;
					break;
				case "Кроссовер":
				case "кроссовер":
					$body = 8;
					break;
				case "Микроавтобус":
				case "микроавтобус":
					$body = 10;
					break;
				case "Лифтбэк":
				case "лифтбэк":
					$body = 4;
					break;
				case "Фастбэк":
				case "фастбэк":
					$body = 4;
					break;
				default:
					$body = "";
					break;
			}
		
		return $body;
		}
//		else
//			echo "Не найдено ", $body, "\n";
		
		return "";
	}
	
	public function format_custom_house_state($custom_house_state)
	{
		switch ($custom_house_state)
		{
			case "растаможен":
				$custom_house_state = 1;
				break;
			case "не растаможен":
				$custom_house_state = 2;
				break;
			default:
				$custom_house_state = "";
				break;
		}
		
		return $custom_house_state;
	}

	public function format_steering_wheel($steering_wheel)
	{
		foreach ($this->steering_wheel_array as $key => $item)
		{
			$find = preg_match("/\b{$steering_wheel}\b/iu", $item);
			
			if ($find)
				return $key;
		}
	}
	
	public function format_photo($photos)
	{
		if (!empty($photos))
		{
			foreach ($photos as $key => $photo)
			{
				$photo_path = explode("/", $photo);
				$photo_path = "/files/tmp/" .$photo_path[1] . "/" . $photo_path[2];
				
				$photos[$key] = [
					"src" => $photo_path,
					"alt" => "",
				];
			}
		}
		
		return $photos;
	}

	public function compare_brand($brand)
	{
		$advert_brand = explode(" ", $brand);
		if (isset($advert_brand[1]))
			$advert_brand = $advert_brand[1];
		else 
			$advert_brand = $advert_brand[0];
	
		if (count(self::$brand_array) > 0)
			$brands = self::$brand_array;
		else
			$brands = $this->brand_array();
		
		foreach ($brands as $brand) 
		{
			$find = preg_match("/\b{$advert_brand}\b/i", $brand["name"]);
			
			if ($find)
				return $brand["id"];
		}
	}
	
	public $table_conformity = [
		"GL-Класс GL 350" => "GL-класс",
		"S-Класс AMG" => "S-класс AMG",
		"S-класс AMG" => "S-класс AMG",
		// Lada
		"2114" => "Samara седан",
		"2107" => "2107",
		"Kalina 1117" => "Kalina универсал",
		// Peugeot
		"207" => "207",
		// Great Wall
		"H3" => "Hover H3",
		// Uz-Daewoo
		"Nexia" => "Nexia",
		// KIA
		"Picanto" => "Picanto",
		"Spectra" => "Spectra",
	];
	
	public function compare_model($brand, $model)
	{
		$advert_brand = $brand;
		
		$advert_model = $model;
		
		if (count(self::$model_array) > 0)
			$models = self::$model_array;
		else 
			$models = $this->model_array();
		
		foreach ($models as $model)
		{
			if (isset($this->table_conformity[$advert_model]))
			{
				$advert_model = $this->table_conformity[$advert_model];
			}
			
			if ($model["brand"] == $advert_brand)
			{
//				echo $model["name"], " === ", $advert_model, "({$advert_brand})";
				$find = preg_match("/^{$advert_model}$/iu", $model["name"]);
//				echo " *** ", $find, "\n";
				if ($find)
					return $model["id"];
			}
		}
		
		return "";
	}
	
	public function all()
	{
		$brands = $this->brand_array();
		$models = $this->model_array();
		
		foreach ($brands as $brand)
		{
			echo $brand["name"], "\n";

			echo "====================\n";
			foreach ($models as $model)
			{
				if ($model["brand"] == $brand["id"])
				{
					echo $model["name"], "\n";
				}
			}
			echo "====================\n";
		}
	}
	
	public function run($args)
	{
		$mongo = new MongoClient();
		$db = $mongo->selectDb("adverts");
		$adverts = new MongoCollection($db, "am_copy");
		$cursor = $adverts->find();
		
		$i = 0;
		foreach ($cursor as $doc) 
		{
			if (isset($doc["advert"]))
			{
				$advert_to_save = [];
				$advert =  json_decode($doc["advert"], TRUE);
				
				$advert_to_save["id"] = $doc["_id"];
				$advert_to_save["url"] = $doc["advert_url"];
				
				// Марка
				if (isset($advert["brand"]))
					$advert_to_save["brand"] = $this->compare_brand($advert["brand"]);
				else 
					$advert_to_save["brand"] = "";
				
				// Модель
				if (isset($advert["model"]))
					$advert_to_save["model"] = $this->compare_model($advert_to_save["brand"], $advert["model"]);
				else 
					$advert_to_save["model"] = "";
				
				// Год
				if (isset($advert["year"]))
					$advert_to_save["year"] = $advert["year"];
				else 
					$advert_to_save["year"] = "";
				
				// Продавец
				if (isset($advert["seller"]))
					$advert_to_save["seller"] = $this->format_seller($advert["seller"]);
				else
					$advert_to_save["seller"] = "нет владельца";
				
				// Фото
				if (isset($advert["images"]))
					$advert_to_save["photo"] = $this->format_photo($advert["images"]);
				else 
					$advert_to_save["photo"] = "";
				
				// Тип кузова
				if (isset($advert["body"]))
					$advert_to_save["body"] = $this->format_body($advert["body"]);
				else
					$advert_to_save["body"] = "";
				
				// Привод
				if (isset($advert["transmission"]))
					$advert_to_save["transmission"] = $this->format_transmission($advert["transmission"]);
				else 
					$advert_to_save["transmission"] = "";
				
				// Руль
				if (isset($advert["steering_wheel"]))
					$advert_to_save["steering_wheel"] = $this->format_steering_wheel($advert["steering_wheel"]);
				else 
					$advert_to_save["steering_wheel"] = "";
				
				// Тип двигателя
				if (isset($advert["engine_type"]))
					$advert_to_save["engine_type"] = $this->format_engine_type($advert["engine_type"]);
				else 
					$advert_to_save["engine_type"] = "";	
				
				// Тип КПП
				if (isset($advert["kpp"]))
					$advert_to_save["kpp"] = $this->format_kpp($advert["kpp"]);
				else 
					$advert_to_save["kpp"] = "";
				
				// Объём
				if (isset($advert["engine_volume"]))
					$advert_to_save["engine_volume"] = $advert["engine_volume"];
				else 
					$advert_to_save["engine_volume"] = "";
				
				// Мощность
				if (isset($advert["engine_power"]))
					$advert_to_save["engine_power"] = $advert["engine_power"];
				else 
					$advert_to_save["engine_power"] = "";
				
				// Цвет
				if (isset($advert["color"]))
					$advert_to_save["color"] = $this->format_color($advert["color"]);
				else 
					$advert_to_save["color"] = "";
				
				// Текст объявления
				if (isset($advert["text"]))
					$advert_to_save["text"] = $advert["text"];
				else 
					$advert_to_save["text"] = "";
				
				// Телефон
				if (isset($advert["phone"]))
					$advert_to_save["phone"] = $this->format_phone($advert["phone"]);
				else 
					$advert_to_save["phone"] = "";
				
				// Цена
				if (isset($advert["price"]))
					$advert_to_save["price"] = $this->format_price($advert["price"]);
				else
					$advert_to_save["price"] = "";
				
				// Регион
				if (isset($advert["region"]))
					$advert_to_save["region"] = $this->format_region($advert["region"]);
				else 
					$advert_to_save["region"] = "";
				
				// Место осмотра
				if (isset($advert["inspection_place"]))
					$advert_to_save["inspection_place"] = $advert["inspection_place"];
				else
					$advert_to_save["inspection_place"] = "";
				
				// Хозяев в ПТС
				if (isset($advert["owners"]))
					$advert_to_save["owners"] = $advert["owners"];
				else 
					$advert_to_save["owners"] = "";
				
				// Состояние
				if (isset($advert["state"]))
					$advert_to_save["state"] = $this->format_state($advert["state"]);
				else 
					$advert_to_save["state"] = "";
				
				// Таможка
				if (isset($advert["custom_house_state"]))
					$advert_to_save["custom_house_state"] = $this->format_custom_house_state($advert["custom_house_state"]);
				else
					$advert_to_save["custom_house_state"] = "";
				
				// VIN
				if (isset($advert["vin"]))
					$advert_to_save["vin"] = $advert["vin"];
				else 
					$advert_to_save["vin"] = "";
				
				// Пробег
				if (isset($advert["distance"]))
					$advert_to_save["distance"] = $this->format_distance($advert["distance"]);
				else 
					$advert_to_save["distance"] = "";
			}
			
			if ($this->check_advert($advert_to_save))
			{
//				print_r($advert_to_save);
//				echo "\n";
				$model = new AdvertCar('create');
				$model->original_url = $doc["advert_url"];
				$model->brand = $advert_to_save["brand"];
				$model->model = $advert_to_save["model"];
				$model->year = $advert_to_save["year"];
				$model->seller = $advert_to_save["seller"];
				$model->photo = $advert_to_save["photo"];
				$model->body = $advert_to_save["body"];
				$model->transmission = $advert_to_save["transmission"];
				$model->steering_wheel = $advert_to_save["steering_wheel"];
				$model->engine_type = $advert_to_save["engine_type"];
				$model->kpp = $advert_to_save["kpp"];
				$model->engine_volume = $advert_to_save["engine_volume"];
				$model->engine_power = $advert_to_save["engine_power"];
				$model->color = $advert_to_save["color"];
				$model->owners = $advert_to_save["owners"];
				$model->state = $advert_to_save["state"];
				$model->custom_house_state = $advert_to_save["custom_house_state"];
				$model->vin = $advert_to_save["vin"];
				$model->phone = $advert_to_save["phone"];
				$model->price = $advert_to_save["price"];
				$model->distance = $advert_to_save["distance"];
				$model->text = $advert_to_save["text"];
				$model->region = $advert_to_save["region"];
				$model->inspection_plase = $advert_to_save["inspection_place"];
				
				$model->post_status = 'p';
				
				if($model->save())
				{
					$i++;
					echo "Сохранено №{$i}\n";
				}
				else
					die('<pre>'.print_r($model->getErrors(), true).'</pre>');
				
			}
//			echo "\n";
//			print_r($advert_to_save["price"]);
		}
//		print_r($this->all());
//		$this->all();
	}
	
}
