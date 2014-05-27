<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);

class AddParsingAdvertsCommand extends CConsoleCommand
{
	/**
	 * Постоянная база со спарсенными объявлениями
	 */
	private $_advertsActualCollection;

	/**
	 * База с последними спарсенными объявлениями
	 */
	private $_advertsOriginalCollection;

	public $tableConformity = [
		// УАЗ
		"20" => [
		],
		// Иж
		"22" => [
			"2126"			=> "2125",
			"2127 Версия"	=> "2126 Версия",
			"2127 Ода"		=> "2126 Ода",
			"2127 Орбита"	 => "2126 Орбита",
			"2127 Фабула"	 => "2126 Фабула",
		],
		// Газ
		"25" => [
			"15 (Чайка)"		=> "14 (Чайка)",
			"Тигр 3"			=> "Тигр 2",
			"Газель"			=> "null",
			"Соболь"			=> "null",
			"Соболь Баргузин"	=> "null",
		],
		// Volvo
		"29" => [
			"V41" => "V40",
		],
		//Uz-Daewoo
		"31" => [
			"Nexia" => "Nexia",
		],
		// Toyota
		"32" => [
			"GT87" => "GT86",
			"RAV5" => "RAV4",
		],
		// Suzuki
		"33" => [
			"SX4"	=> "New SX4",
			"SX5"	=> "SX4 седан",
			"X-91"	=> "X-90",
			"XL7"	=> "XL8",
		],
		// Subaru
		"34" => [
			"R3" => "R2",
		],
		// Rolls-Royce
		"40" => [
			"401 (RT)"				=> "400 (RT)",
			"401 Hatchback (RT)"	=> "400 Hatchback (RT)",
			"46 (RT)"				=> "45 (RT)",
			"76 (RJ)"				=> "75 (RJ)",
		],
		// Renault
		"41" => [
			"Megane RS" => "Megane RS",
		],
		// Peugeot
		"43" => [
			"207" => "207",
		],
		// Opel
		"44" => [
			"Astra GTC"		=> "Astra GTC",
			"Vectra OPC"	=> "",
		],
		// Mitsubishi
		"46" => [
			"3001 GT"	=> "3000 GT",
			"L201"		=> "L200",
		],
		// Mercedes-Benz
		"48" => [
			"A-Класс A 140"			=> "A-класс",
			"GL-Класс GL 350"		=> "GL-класс",
			"S-Класс AMG"			=> "S-класс AMG",
			"S-класс AMG"			=> "S-класс AMG",
			"GL-Класс GL 450"		=> "GL-класс",
			"B-Класс B 180"			=> "B-класс",
			"C-Класс C 180"			=> "C-класс",
			"C-Класс C 240"			=> "C-класс",
			"CL-Класс CL 500"		=> "CL-класс",
			"C-Класс C 220"			=> "C-класс",
			"E-Класс E 230"			=> "E-класс",
			"E-Класс E 200"			=> "E-класс",
			"E-Класс E 220"			=> "E-класс",
			"E-Класс E 300"			=> "E-класс",
			"G-Класс G 500"			=> "G-класс 5 дверей",
			"SLK-Класс SLK 200"		=> "SLK-класс",
			"SLK-Класс SLK 230"		=> "SLK-класс",
		],
		// Mazda
		"49" => [
			"930"				=> "929",
			"BT-51"				=> "BT-50",
			"MX-6"				=> "MX-5",
			"RX-9"				=> "RX-9",
			"Bongo Friendee"	=> "Bongo",
		],
		// Maserati
		"50" => [
			"229" => "228",
			"3201 GT" => "3200 GT",
		],
		// Lifan
		"53" => [
			"X61" => "X60",
		],
		// Lexus
		"54" => [
			"LX LX470" => "LX",
			"RX RX300" => "RX",
			"RX RX330" => "RX",
			"RX RX350" => "RX",
			"LS LS430" => "LS",
			"GS GS300" => "GS",
			"GX GX460" => "GX",
		],
		// Lada
		"57" => [
			"1111 Ока"			=> "1111",
			"1111 Ока 11113"	=> "1111",
			"Kalina 1118"		=> "Kalina",
			"Kalina 1119"		=> "Kalina",
			"2101 21011"		=> "2101",
			"2104 21041"		=> "2104",
			"2104 21043"		=> "2104",
			"2108 21083"		=> "2108",
			"2109 21093"		=> "2109",
			"2110 21100"		=> "2110",
			"2110 21101"		=> "2110",
			"2110 21102"		=> "2110",
			"2110 21103"		=> "2110",
			"2110 21104"		=> "2110",
			"2111 21110"		=> "2111",
			"2111 21111"		=> "2111",
			"2112 21120"		=> "2112",
			"2112 21122"		=> "2112",
			"2112 21124"		=> "2112",
			"2114 21144"		=> "Samara хэтчбек 5 дверей",
			"2107 21073"		=> "2107",
			"2105 21053"		=> "2105",
			"2111 21112"		=> "2111",
			"2111 21113"		=> "2111",
			"2112 21121"		=> "2112",
			"2106 21063"		=> "2106",
			"4x4 21213"			=> "4x4 3 двери",
			"4x4 21214"			=> "4x4 3 двери",
			"4x4 2131"			=> "4x4 5 дверей",
			"2115"				=> "Samara",
			"2113"				=> "Samara хэтчбек 3 двери",
			"2114"				=> "Samara хэтчбек 5 дверей",
			"2109 21099"		=> "21099",
			"2107"				=> "2107",
			"Kalina 1117"		=> "Kalina",
			"Priora 2170"		=> "Priora",
			"Priora 2171"		=> "Priora",
			"Priora 2172"		=> "Priora",
			"Priora 2173"		=> "Priora",
			"Priora хэтчбек"	=> "Priora",
			"2107 21074"		=> "2107",
			'2110 21108 "Премьер"' => "2110",
			"Priora 2170 седан" => "Priora",
		],
		// KIA
		"58" => [
			"Picanto"	=> "Picanto",
			"Spectra"	=> "Spectra",
			"Cee'd"		=> "Pro_ceed",
		],
		// Infiniti
		"61" => [
			"I-Series"			=> "I",
			"M-Series"			=> "M",
			"EX-Series"			=> "QX50",
			"FX-Series"			=> "QX70",
			"FX-Series FX35"	=> "QX70",
			"QX-Series"			=> "QX80",
		],
		// Hyundai
		"62" => [
			"H2"		=> "H1",
			"Trajet"	=> "Trajet (FO)",
		],
		// Honda
		"64" => [
			"Civic Type-R" => "Civic Type-R",
		],
		// Great Wall
		"66" => [
			"M2" => "Hover M2",
			"H3" => "Hover H3",
			"H5" => "Hover H5",
			"H6" => "Hover H6",
		],
		// Fiat
		"69" => [
			"601" => "600",
		],
		// Ferrari
		"70" => [
			"458" => "458 Italia",
			"459" => "458 Spider",
		],
		// Citroen
		"73" => [
			"3 CV"			=> "2 CV",
			"C3 Picasso"	=> "C3 Picasso",
		],
		// Chevrolet
		"75" => [
			"Aveo седан" => "Aveo Sedan",
		],
		// Chery
		"76" => [
			"B12" => "B11",
		],
		// BMW
		"80" => [
			"1 серия 116"	=> "1 Серии",
			"3 серия 316"	=> "3 Серии",
			"3 серия 318"	=> "3 Серии",
			"3 серия 320"	=> "3 Серии",
			"3 серия 325"	=> "3 Серии",
			"5 серия 518"	=> "5 Серии",
			"5 серия 523"	=> "5 Серии",
			"5 серия 525"	=> "5 Серии",
			"5 серия 528"	=> "5 Серии",
			"5 серия 540"	=> "5 Серии",
			"7 серия"		=> "7 Серии",
			"7 серия 728"	=> "7 Серии",
			"7 серия 750"	=> "7 Серии",
		],
		// Bentley
		"81" => [
			"Continental" => "Continental GT",
		],
		// Audi
		"82" => [
			"A6 Allroad quattro" => "A6 allroad quattro",
		],
		// Lyxgen
		"57350" => [
			"8 SUV" => "7 SUV",
		],
		// Ford
		"6692" => [
			"F-151" => "F-150",
			"C-MAX" => "Focus C-MAX",
		],
	];

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

	public $requiredParams = [
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
//		"engine_power",
//		"color",
		"phone",
		"price",
		"distance",
//		"text",
		"region",
	];

	public $savingData = [
//        "original_url",
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
		"owners",
		"state",
		"custom_house_state",
		"vin",
		"phone",
		"price",
		"distance",
		"text",
		"region",
		"inspection_plase",
	];

	protected function advertDuplicateExists($advertToSave)
	{
		$attributes = [
			"seller"			=> $advertToSave["seller"],
			"brand"				=> $advertToSave["brand"],
			"model"				=> $advertToSave["model"],
			"year"				=> $advertToSave["year"],
			"body"				=> $advertToSave["body"],
			"transmission"		=> $advertToSave["transmission"],
			"steering_wheel"	=> $advertToSave["steering_wheel"],
			"engine_type"		=> $advertToSave["engine_type"],
			"phone"				=> $advertToSave["phone"],
			"price"				=> $advertToSave["price"],
			"distance"			=> $advertToSave["distance"],
			"hidden"			=> 0,
		];

		$models = AdvertCar::model()->findAllByAttributes($attributes);

		if (count($models) >= 1)
			return true;

		return false;
	}

	protected function brandArray()
	{
		$brand_array = Yii::app()
			->db
			->createCommand("SELECT id, name FROM brand_new_car WHERE hidden=0 ORDER BY name")
			->queryAll();
		return $brand_array;
	}

	protected function modelArray()
	{
		$model_array = Yii::app()
			->db
			->createCommand("SELECT id, name, brand FROM model_new_car WHERE hidden=0 ORDER BY name")
			->queryAll();
		return $model_array;
	}

	protected function validateAdvert($advert)
	{
		foreach ($this->requiredParams as $requiredParam)
		{
			if (empty($advert[$requiredParam])
				&& (($advert[$requiredParam] != " ") || ($advert[$requiredParam] != 0)))
				return false;
		}

		return true;
	}

	protected function formatDistance($distance)
	{
		$distance = str_replace(" ", "", $distance);
		preg_match_all('/[\d]+/', $distance, $matches);
		$distance = implode("", $matches[0]);

		return $distance;
	}

	protected function formatPrice($price)
	{
		$price = str_replace(" ", "", $price);

		return $price;
	}

	protected function formatPhone($phone)
	{
		$phone = str_replace(" ", "", $phone);
		$phone = str_replace("Б", "6", $phone);

		if ((strlen($phone) > 0) && ($phone[0] == "("))
			$phone = "8" . $phone;

		$phone = str_replace("(", "-", $phone);
		$phone = str_replace(")", "-", $phone);

		return $phone;
	}

	protected function formatRegion($region)
	{
		$region = preg_replace("/\s\s+/", "", $region);

		return ucfirst(strtolower($region));
	}

	protected function formatSeller($seller)
	{
		$exceptionSeller = [
			'Автостайл',
			'АвтоСтайл',
			'БЦР Моторс', 
			'АвтоРегион НН',
		];

		$seller = preg_replace("/\s\s+/", "", $seller);
		$seller = str_replace("\n", "", $seller);
		$seller = trim($seller);

		if (in_array($seller, $exceptionSeller))
				return "";

		if (count(explode(" ", $seller)) > 1)
			return $seller;
		else
			return ucfirst(strtolower($seller));
	}

	protected function formatColor($color)
	{
		$color = str_replace("  ", "", $color);

		return strtolower($color);
	}

	protected function formatTransmission($transmission)
	{
		foreach ($this->transmission_array as $key => $item)
		{
			$find = preg_match("/\b{$transmission}\b/iu", $item);

			if ($find)
				return $key;
		}
	}

	protected function formatEngineType($engine_type)
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

	protected function formatKpp($kpp)
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

	protected function formatState($state)
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

	protected function formatBody($body)
	{
		$pattern = "/\b(седан|универсал|пикап|хетчбэк|кабриолет|купе|фургон|внедорожник|лимузин|минивэн|автобус|кроссовер|микроавтобус|лифтбэк|фастбэк)\b/iu";
		preg_match($pattern, $body, $matches);


		if (isset($matches[0]))
		{
			$find = trim(strtolower($matches[0]));

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
				case "хэтчбек":
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

			if (isset($this->body_array[$body]))
				return $body = [
					"id" => $body,
					"name" => $this->body_array[$body],
				];
		}

		return "";
	}

	protected function formatCustomHouseState($custom_house_state)
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

	protected function formatSteeringWheel($steering_wheel)
	{
		foreach ($this->steering_wheel_array as $key => $item)
		{
			$find = preg_match("/\b{$steering_wheel}\b/iu", $item);

			if ($find)
				return $key;
		}
	}

	protected function formatPhoto($photos)
	{
		if (!empty($photos))
		{
			foreach ($photos as $key => $photo)
			{
				$photoPath = explode("/", $photo);
				$photoPath = "/files/tmp/" .$photoPath[0] . "/" . $photoPath[1];

				$photos[$key] = [
					"src" => $photoPath,
					"alt" => "",
				];
			}
		}

		return $photos;
	}

	protected function compareBrand($brand)
	{
		$exceptionBrands = ['ГАЗ'];

		$advert_brand = explode(" ", $brand);
		if (isset($advert_brand[1]))
			$advert_brand = $advert_brand[1];
		else
			$advert_brand = $advert_brand[0];

		if (in_array($advert_brand, $exceptionBrands))
			return;

		$brands = $this->brandArray();

		foreach ($brands as $brand)
		{
			$find = preg_match("/\b{$advert_brand}\b/i", $brand["name"]);

			if ($find)
				return $brand["id"];
		}
	}

	protected function compareModel($brand, $model, $body)
	{
		// из объявления
		$advert_brand = $brand;
		$advert_model = $model;

		// с drivenn
		$models = $this->modelArray();

		foreach ($models as $model)
		{
			if ($model["brand"] == $advert_brand)
			{
				if (isset($this->tableConformity[$advert_brand][$advert_model]))
					$advert_model = $this->tableConformity[$advert_brand][$advert_model];

				$find_without_body = preg_match("/^{$advert_model}$/iu", $model["name"]);

				if ($find_without_body)
					return $model["id"];

				$find_with_body = preg_match("/\b{$advert_model}\s+{$body}\b/iu", $model["name"]);

				if ($find_with_body)
					return $model["id"];
			}
		}

		return "";
	}

	protected function advertNormalization($advert)
	{
		// Тип кузова
		if (isset($advert["body"]))
		{
			$advertToSave["body"] = $this->formatBody($advert["body"]);
			$advertToSave["body_name"] = !empty($advertToSave["body"]["name"]) ? $advertToSave["body"]["name"] : "";
			$advertToSave["body"] = !empty($advertToSave["body"]["id"]) ? $advertToSave["body"]["id"]: "";
		}
		else
			$advertToSave["body"] = "";
		// Марка
		$advertToSave["brand"] = isset($advert["brand"]) ? $this->compareBrand($advert["brand"]) : "";
		// Модель
		$advertToSave["model"] = isset($advert["model"]) ? $this->compareModel($advertToSave["brand"], $advert["model"], $advertToSave["body_name"]) : "";
		// Год
		$advertToSave["year"] = isset($advert["year"]) ? $advert["year"] : "";
		// Продавец
		$advertToSave["seller"] = isset($advert["seller"]) ? $this->formatSeller($advert["seller"]) : "";
		// Фото
		$advertToSave["photo"] = isset($advert["images"]) ? $this->formatPhoto($advert["images"]) : "";
		// Привод
		$advertToSave["transmission"] = isset($advert["transmission"]) ? $this->formatTransmission($advert["transmission"]) : "";
		// Руль
		$advertToSave["steering_wheel"] = isset($advert["steering_wheel"]) ? $this->formatSteeringWheel($advert["steering_wheel"]) : "";
		// Тип двигателя
		$advertToSave["engine_type"] = isset($advert["engine_type"]) ? $this->formatEngineType($advert["engine_type"]) : "";
		// Тип КПП
		$advertToSave["kpp"] = isset($advert["kpp"]) ? $this->formatKpp($advert["kpp"]) : "";
		// Объём
		$advertToSave["engine_volume"] = isset($advert["engine_volume"]) ? $advert["engine_volume"] : "";
		// Мощность
		$advertToSave["engine_power"] = isset($advert["engine_power"]) ? $advert["engine_power"] : "";
		// Цвет
		$advertToSave["color"] = isset($advert["color"]) ? $this->formatColor($advert["color"]) : "";
		// Текст объявления
		$advertToSave["text"] = isset($advert["text"]) ? $advert["text"] : "";
		// Телефон
		$advertToSave["phone"] = isset($advert["phone"]) ? $this->formatPhone($advert["phone"]) : "";
		// Цена
		$advertToSave["price"] = isset($advert["price"]) ? $this->formatPrice($advert["price"]) : "";
		// Регион
		$advertToSave["region"] = isset($advert["region"]) ? $this->formatRegion($advert["region"]) : "";
		// Место осмотра
		$advertToSave["inspection_plase"] = isset($advert["inspection_place"]) ? $advert["inspection_place"] : "";
		// Хозяев в ПТС
		$advertToSave["owners"] = isset($advert["owners"]) ? $advert["owners"] : "";
		// Состояние
		$advertToSave["state"] = isset($advert["state"]) ? $this->formatState($advert["state"]) : "";
		// Таможка
		$advertToSave["custom_house_state"] = isset($advert["custom_house_state"]) ? $this->formatCustomHouseState($advert["custom_house_state"]) : "";
		// VIN
		$advertToSave["vin"] = isset($advert["vin"]) ? $advert["vin"] : "";
		// Пробег
		$advertToSave["distance"] = isset($advert["distance"]) ? $this->formatDistance($advert["distance"]) : "";

		return $advertToSave;
	}

	protected function advertSave($advert)
	{
		$messageStatus = ($advert->post_status == 'p') ? "Сохранено|Опубликовано" : "Сохранено|Модерация";
//		echo "{$messageStatus} \n";
		if($advert->save())
			echo "{$messageStatus} \n";
		else
		{
			echo $advert->scenario;
			die('<pre>'.print_r($advert->getErrors(), true).'</pre>');
		}
	}

	protected function advertUpdate($advert, $advertToSave)
	{
		echo "id = {$advert->id} ";

		$advert->price = $advertToSave["price"];
		$advert->distance = $advertToSave["distance"];
		$advert->phone = $advertToSave["phone"];

		if ($advert->scenario == 'parsing_advert')
			$advert->photo = $advertToSave["photo"];

		echo "Обновляем... ";

		$this->advertSave($advert);
	}

	protected function advertExists($original_url)
	{
		$advert = AdvertCar::model()->findByAttributes(['original_url' => $original_url, 'hidden' => 0]);

		return $advert;
	}

	protected function removeIrrelevantAdverts()
	{
		$advertActualUrl = [];
		$collection = $this->_advertsActualCollection->find(["status" => "actual"]);

		foreach ($collection as $document)
		{
			$advertActualUrl[] = $document["advert_url"];
		}

		if (count($advertActualUrl) < 1)
			return false;

		$originalUrl = Yii::app()->db->createCommand()
			->select('id, original_url')
			->from('advert_car')
			->where('original_url <> " " AND hidden = 0')
			->queryAll();

		$count = 0;
		foreach ($originalUrl as $url)
		{
			if (!in_array($url["original_url"], $advertActualUrl))
			{
				$count++;
				echo "{$count} ";
				echo "Объявление с атрибутом original_url = {$url["original_url"]} не актуально \n";
				Yii::app()->db->createCommand()
					->update('advert_car',
						['hidden' => 1,],
						'id=:id',
						[':id'=>$url["id"]]
					);
			}
		}
	}

	protected function addNewAdvert($advert, $advertUrl, $hash)
	{
		if ($this->validateAdvert($advert) == false)
		{
			if (empty($advert["photo"]) || empty($advert["seller"]))
				return false;

			if ($model = $this->advertExists($advertUrl))
			{
				$model->scenario = 'parsing_advert_moderation';
				$this->advertUpdate($model, $advert);
			}
			else
			{
				$model = new AdvertCar('parsing_advert_moderation');

				foreach ($this->savingData as $param)
					$model->$param = $advert[$param];

				$model->original_url = $advertUrl;

				$this->advertSave($model);
			}
		}
		else
		{
			if (!$this->advertDuplicateExists($advert))
			{
				echo 'Спарсенное объявление не найдено в числе собственных объявлений', "\n";

				if ($model = $this->advertExists($advertUrl))
				{
					echo 'Обновляем объявление т.к. оно уже существует', "\n";

					$model->scenario = 'parsing_advert';
					$this->advertUpdate($model, $advert);
				}
				else
				{
					echo 'Записываем новое спарсенное объявление', "\n";

					$model = new AdvertCar('parsing_advert');

					foreach ($this->savingData as $param)
						$model->$param = $advert[$param];

					$model->original_url = $advertUrl;
					$model->post_status = 'p';

					$this->advertSave($model);
				}
			}
		}

		$mongoData = [
			"advert_url" => $advertUrl,
			"advert" => json_encode($advert), 
			"hash" => $hash, 
			"status" => "actual"
		];
		$this->_advertsActualCollection->save($mongoData);
	}

	protected function deadLine($date)
	{
		$endDate = \DateTime::createFromFormat('d.m.Y', $date);
		$currentDate = \DateTime::createFromFormat('d.m.Y', date('d.m.Y'));
		$interval = $currentDate->diff($endDate);

		if ($interval->days < 7)
			return true;

		return false;
	}
	
	protected function updateActualCollection($advertUrl, $hash = null)
	{
		if (is_null($hash))
			$mongoData = ['$set' => ["status" => "actual"]];
		else
			$mongoData = ['$set' => ["hash" => $hash, "status" => "actual"]];
		
		$mongoCriteria = ["advert_url" => $advertUrl];
		$mongoModificator = ["upsert" => true];
		
		$this->_advertsActualCollection->update($mongoCriteria, $mongoData, $mongoModificator);
	}

	public function init()
	{
		$mongoConnection = new MongoClient("mongodb://localhost");
		$mongoDb = $mongoConnection->selectDb("adverts");
		
		$this->_advertsActualCollection = new MongoCollection($mongoDb, "drivenn_adverts");
		$this->_advertsOriginalCollection = new MongoCollection($mongoDb, "am_ru_adverts");
		
		$this->_advertsActualCollection->update([], ['$set' => ["status" => "not_actual"]], ["upsert" => false, "multiple" => true]);
	}

	public function run($args)
	{
		$documents = $this->_advertsOriginalCollection->find();

		foreach ($documents as $document)
		{
			if (empty($document["advert"]))
				continue;

			$advert =  json_decode($document["advert"], true);
			$advertUrl = $document["advert_url"];
			$advertHash = $document["hash"];

			$actualAdvertCursor = $this->_advertsActualCollection->find(["advert_url" => $advertUrl])->limit(1);
			$actualAdvert = $actualAdvertCursor->current();
			
			// если объявление не существует или если существует, но хэши не совпадают
			if (is_null($actualAdvert) || (isset($actualAdvert["hash"]) && ($actualAdvert["hash"] !== $advertHash)))
			{
				$actualAdvert = $this->advertNormalization($advert);
				$model = AdvertCar::model()->findByAttributes(["original_url" => $advertUrl]);

				if (!$model)
				{
					$this->addNewAdvert($actualAdvert, $advertUrl, $advertHash);
					continue;
				}
				
//				if ($model->post_status == 'h' && $model->hidden == 0)
//					$model->post_status = 'p';
				
				if ($model->post_status == "p")
				{
					$model->scenario = 'parsing_advert';
					$this->advertUpdate($model, $actualAdvert);
					
					$this->updateActualCollection($advertUrl, $advertHash);
				}

				if ($model->post_status == 'm')
					$this->updateActualCollection($advertUrl, $advertHash);
			}
			// если объявление существует и хеши совпадают
			else
			{
				echo "объявление существует и хеши совпадают \n";
				$model = AdvertCar::model()->findByAttributes(["original_url" => $advertUrl]);
				
				if (!$model)
					continue;
				
//				if ($model->post_status == 'h' && $model->hidden == 0)
//					$model->post_status = 'p';
				
				if ($model->post_status == "p")
				{
					if ($this->deadLine($model->obj_show_date) === true)
					{
						echo $model->obj_show_date, ' - продлеваем', "\n";
						
						$model->scenario = 'parsing_advert';
						$model->obj_show_date = date('Y-m-d', strtotime('+ 14 day'));
						
						echo 'Продлеваем объявление id = ', $model->id, " до ", $model->obj_show_date, "\n";
						
						$this->advertSave($model);
					}
					echo "Дата скрытия объекта id = ", $model->id, " = ", $model->obj_show_date, "\n";
					
					$this->updateActualCollection($advertUrl);
				}

				if ($model->post_status == 'm')
					$this->updateActualCollection($advertUrl);
			}
		}

		$this->removeIrrelevantAdverts();
	}

}
