<?php
class Braintree_AddOn extends Braintree_Modification
{
    public static function all()
    {
        $response = Braintree_Http::get('/add_ons');

        $addOns = array("addOn" => $response['addOns']);

        return Braintree_Util::extractAttributeAsArray(
            $addOns,
            'addOn'
        );
    }
}
