<?php
namespace Garan24\Delivery\BoxBerry;
use \Garan24\Deal\Deal as Deal;
class Converter{
    protected $bb_data = false;
    protected $grn_data;
    public function __construct(){

    }
    public function convert($o,$brutto="2000"){
        if(!$o instanceof Deal) {
            throw new \Exception("Wrong object");
        }
        $this->grn_data = $o;
        $c = $o->getCustomer();
        $this->bb_data=[
            'method' => "ParcelCreateForeign",      // Название метода (Обязателен)
            'token' => '18455.rvpqeafa',            // Ваш API token (Обязателен)
            'u_name' => $c->first_name,                     // Имя получателя (Обязателен)
            'u_surname' =>$c->last_name,                // Фамилия получателя (Обязателен)
            'u_middlename' => $c->fio_middle,           // Отчество получателя (не обязателен)
            'u_email' => $c->user_email,           // Электронная почта (не обязателен)
            'u_phone' => $c->phone,        // Телефон получателя (Обязателен)
            'u_country_code' => 643,                // Код страны паспорта получателя. По умолчанию 643 - Россия. (Обязателен)
            'u_pasport' => $c->passport["series"]." ".$c->passport["number"],           // Номер паспорта получателя (Обязателен)
            'u_passportIssued'=>$c->passport["date"],       // Дата выдачи паспорта (Обязателен)
            'u_passportIssuedBy'=>$c->passport["where"],               // Кем выдан паспорт (Обязателен)
            'u_destination_country_code' => '643',     // Код страны назначения (Обязателен)
            'u_city' => $c->shipping_address["city"],                         // Город получателя (Обязателен)
            'u_take' => ($o->delivery["id"]==6)?'kd':$c->shipping_address["state"],                       // kd если курьерская доставка, или код ПВЗ (Обязателен)
            'u_post_code' => $c->shipping_address["postcode"],              // XXXXXX - индекс города (не обязателен, если  вид доставки ПВЗ)
            'u_street' => $c->shipping_address["address_1"], // Адрес получателя (не обязателен, если  вид доставки ПВЗ)
            'Order' => $o->order->id,                 // Номер заказа Интернет-магазина (не обязателен, в случае если не заполнено, присваивается Sender tracking)
            //'sender_tracking' => '',                // Номер для отслеживания Интернет-магазина (не обязателен, если не заполнено, присваивается Boxberry tracking)
            //'add_barcode128' => 'XXXXXXXXXX',       // Баркод Интернет-магазина для печати на этикетке (Вывод на этикетке – EAN, не обязателен)
            'box' => [
                'x' => '40',                    // Размеры корбоки в см
                'y' => '30',
                'z' => '20',
                'weight_bruto' => $brutto,        // Брутто вес коробки в граммах Обязателен
                'add_tracking_code' => '',      // Дополнительный трекинг код для личных нужд отправителя]
                "items"=>[]
            ]
        ];
        $netto = $brutto/count($o->order->items);
        foreach($o->order->items as $itm){
            $bi = [
                'articul' =>$itm->id,                    // Артикул в магазине (Обязателен)
                'Manufacturer' => '',               // Производитель (не обязателен, No name, если не заполнено)
                'model' => $itm->name,                      // Наименование модели (Обязателен)
                'quantity' => $itm->quantity,                         // Количество (Обязателен)
                'netto' => $netto,                          // Вес нетто в граммах (не обязателен)
                'item_price' => $itm->subtotal,                     // Цена в валюте поставщика (Обязателен)
                'currency_price' => 'RUB',                 // Валюта поставщика EUR GBP USD RUB. При ошибочном написании или пустом поле считается EUR (Обязателен)
                'link_web' => $itm->permalink,                          // Ссылка на товар в интернет (не обязателен)
                'link_foto' => $itm->featured_src,                         // Ссылка на товар в интернет (не обязателен)
                'country_of_origin' => '',                 // Страна происхождения товара (не обязателен)
                'invoice' => $o->order->id."#".$itm->id,               // Номер заказа (Обязателен)
                'descr_rus' => 'Товар с '.$itm->permalink,  // Краткое описание товара на русском (не обязателен)
                'descr_alt' => $itm->description,               // Краткое Описание товара на языке поставщика или на английском (Обязателен)
                'descr_alt_eng' => true,                   // Если описание на английском языке необходимо передать true
            ];
            array_push($this->bb_data["box"] ['items'],$bi);
        }
        return $this->bb_data;
    }
    public function convertRu($o){
        if(!$o instanceof Deal) {
            throw new \Exception("Wrong object");
        }
        $this->grn_data = $o;
        //$this->bb_data['updateByTrack']='Трекинг-код ранее созданной посылки';
        $this->bb_data['order_id']=$o->order->internal_order_id;//'ID заказа в ИМ';
        //$this->bb_data['PalletNumber']='Номер палеты';
        //$this->bb_data['barcode']='Штрих-код заказа';
        $this->bb_data['price']=$o->order->order_total;//'Объявленная стоимость';
        $this->bb_data['payment_sum']=0;//'Сумма к оплате';
        $this->bb_data['delivery_sum']=0;//'Стоимость доставки';
        $this->bb_data['vid']=1;//'Тип доставки (1/2)';
        $this->bb_data['shop']=[
           'name'=>'Код ПВЗ',
           'name1'=>'Код пункта поступления'
        ];
        $this->bb_data['customer']=array(
            'fio'=>'ФИО получателя',
            'phone'=>'Номер телефона',
            'phone2'=>'Доп. номер телефона',
            'email'=>'E-mail для оповещений',
            'name'=>'Наименование организации',
            'address'=>'Адрес',
            'inn'=>'ИНН',
            'kpp'=>'КПП',
            'r_s'=>'Расчетный счет',
            'bank'=>'Наименование банка',
            'kor_s'=>'Кор. счет',
            'bik'=>'БИК'
        );
        $this->bb_data['kurdost'] = array(
            'index' => 'Индекс',
            'citi' => 'Город',
            'addressp' => 'Адрес получателя',
            'timesfrom1' => 'Время доставки, от',
            'timesto1' => 'Время доставки, до',
            'timesfrom2' => 'Альтернативное время, от',
            'timesto2' => 'Альтернативное время, до',
            'timep' => 'Время доставки текстовый формат',
            'comentk' => 'Комментарий'
        );
        $this->bb_data['items']=array(
            array(
                'id'=>'ID товара в БД ИМ',
                'name'=>'Наименование товара',
                'UnitName'=>'Единица измерения',
                'nds'=>'Процент НДС',
                'price'=>'Цена товара',
                'quantity'=>'Количество'
            )
        );
        $this->bb_data['weights']=array(
            'weight'=>'Вес 1-ого места',
            'weight2'=>'Вес 2-ого места',
            'weight3'=>'Вес 3-его места',
            'weight4'=>'Вес 4-ого места',
            'weight5'=>'Вес 5-ого места'
        );
        return $this->bb_data;
    }
};
?>
