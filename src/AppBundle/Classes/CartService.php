<?php

namespace AppBundle\Classes;
class CartService
{
    protected $Session;
    protected $Doctrine;
    protected $max_quantity;
    protected $price;
    protected $pricing;
    protected $TransactionService;

    function __construct($Doctrine, $Session)
    {  
        $this->Doctrine = $Doctrine;
        $this->Session = $Session;
        $settings = $this->Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');
        $pricing = $this->Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('pricing');
        $pricing_array = empty($pricing['array']) ? null : $pricing['array'];
        $this->pricing = empty($pricing_array) ? [0=>['denomination'=>1,'quantity'=>1]] : json_decode($pricing_array, true);
        $machine_id = $Session->get('Machine');
        $Machine = $Doctrine->getRepository('AppBundle:Machine')->find($machine_id);
        $prints = $Machine->getPaper();
        $this->max_quantity = $prints > 50 ? 50 : $prints;
        if(empty($prints))
        {
            $status = $Machine->getStatus();
            die(print_r($status));
            throw new \Exception('Machine out of service.');
        }
    }

    public function modifyMediaQuantity($id, $mode, $images = [], $data = [])
    {
        $cart_data = $this->getCart();
        $slot = empty($cart_data['total']) ? 0 : $cart_data['total'];
        $added = 0;
        if($mode == 'remove')
        {
            if(!empty($cart_data['media'][$id]['quantity']))
            {
                $cart_data['total'] = $cart_data['total'] > 0 ?  --$cart_data['total'] : 0;
                $cart_data['media'][$id]['quantity'] = $cart_data['media'][$id]['quantity'] > 0 ? --$cart_data['media'][$id]['quantity'] : 0;                

                if($cart_data['total'] != $slot)
                    $slot = $cart_data['total'];
            }
        }
        else
        {
            if(empty($cart_data['media'][$id]))
            {
                $cart_data['media'][$id] = ['quantity' => 0, 'images'=>[], 'orientation'=>[], 'rotation' => [], 'data'=>[]];
            }

            if($cart_data['total'] < $this->max_quantity)
            {
                $cart_data['total']++;
                $cart_data['media'][$id]['quantity']++;                        
            }
            
            if($cart_data['media'][$id]['quantity'] > 0)
            {
                $users = '';
                if(isset($data['users']) && !empty($data['users'])) 
                {
                    if(count($data['users']) > 0)
                    {
                        for($i = 0; $i < count($data['users']); $i++)
                        {
                            $users .= '@' . $data['users'][$i]['user']['username'];
                            if($i < (count($data['users']) -1))
                                $users .= ',';
                        }
                    }
                }
                $data['users'] = $users;
                $cart_data['media'][$id]['images'][$slot] = $images;               
                $cart_data['media'][$id]['data'][$slot] = $data; 
            }

        }

        $cart_data['price'] = $this->getPrice($cart_data['total']);

        $cart_data['max_quantity'] = $this->max_quantity;
        $this->Session->set('cart_data', $cart_data);
        return $cart_data;
    }

    public function modifyOrientation($value, $target,$slot)
    {
        $cart_data = $this->getCart();
        if(!isset($cart_data['media'][$target]))
            throw new \Exception('Media object not found.');
    
        $cart_data['media'][$target]['orientation'][$slot] = $value;
        $this->saveCart($cart_data);
        return $cart_data['media'][$target];
    }

    public function modifyRotation($value, $target, $slot)
    {
        $cart_data = empty($this->Session->get('cart_data')) ? $this->initializeCart() : $this->Session->get('cart_data');
        if(!isset($cart_data['media'][$target]))
            throw new \Exception('Media object not found.');
   
        $cart_data['media'][$target]['rotation'][$slot] = empty($cart_data['media'][$target]['rotation'][$slot]) ? $value : $cart_data['media'][$target]['rotation'][$slot] + $value;
        $cart_data['media'][$target]['rotation'][$slot] = $cart_data['media'][$target]['rotation'][$slot] > 3 ? $cart_data['media'][$target]['rotation'][$slot] = 0 : $cart_data['media'][$target]['rotation'][$slot];
        $cart_data['media'][$target]['rotation'][$slot] = $cart_data['media'][$target]['rotation'][$slot] < 0 ? $cart_data['media'][$target]['rotation'][$slot] = 3 : $cart_data['media'][$target]['rotation'][$slot];

        $this->saveCart($cart_data);
        return $cart_data['media'][$target];
    }


    public function modifyData($value, $target, $slot, $mode)
    {
        $cart_data = $this->getCart();
        if(!isset($cart_data['media'][$target]))
            throw new \Exception('Media object not found.');

        if(!isset($cart_data['media'][$target]['data'][$slot][$mode]))
            $cart_data['media'][$target]['data'][$slot][$mode] = [];

        if($mode != 'location')      
            $cart_data['media'][$target]['data'][$slot][$mode] = $value;
        else
        {
            if(!isset($cart_data['media'][$target]['data'][$slot]['gps']['name']))
                $cart_data['media'][$target]['data'][$slot]['gps'] = ['name'=>''];

            $cart_data['media'][$target]['data'][$slot]['gps']['name'] = $value;
        }

        $this->saveCart($cart_data);
        return $value;
    } 

    public function deleteCartItem($id, $slot)
    {
        $cart_data = $this->getCart();
        if(!isset($cart_data['media'][$id]))
            throw new \Exception('Media object not found.');

        if($cart_data['media'][$id]['quantity'] < 2) 
        {
            unset($cart_data['media'][$id]);
        }
        else
        {
            unset($cart_data['media'][$id]['data'][$slot]);
            unset($cart_data['media'][$id]['images'][$slot]);
            unset($cart_data['media'][$id]['orientation'][$slot]);
            $cart_data['media'][$id]['quantity']--;
        }

        $cart_data['total']--;

        $this->saveCart($cart_data);
        return $cart_data;
       
    }

    public function reSlot($cart_data)
    {
        $slot = 0;
        if(!empty($cart_data['media']))
        {
            foreach($cart_data['media'] as $id=>$media)
            {               
                foreach($cart_data['media'][$id]['data'][$id] as $oldslot=>$data)
                {
                    unset($cart_data['media'][$id]['data'][$id]['data'][$oldslot]);
                    $cart_data['media'][$id]['data'][$id]['data'][$slot] = $data;
                }

                foreach($cart_data['media'][$id]['images'][$id] as $oldslot=>$images)
                {
                    unset($cart_data['media'][$id]['images'][$id]['images'][$oldslot]);
                    $cart_data['media'][$id]['images'][$id]['images'][$slot] = $images;
                }

                foreach($cart_data['media'][$id]['orientation'][$id] as $oldslot=>$data)
                {
                    unset($cart_data['media'][$id]['orientation'][$id]['data'][$oldslot]);
                    $cart_data['media'][$id]['orientation'][$id]['data'][$slot] = $data;
                }            
            }
        }
        return $cart_data;
    }

    public function initializeCart()
    {
        $cart_data = ['total'=>0, 'media'=>[], 'max_quantity'=>$this->max_quantity, 'price'=>$this->getPrice(0), 'discount'=>['amount'=>0,'code'=>null]];
        $this->saveCart($cart_data);
        return $cart_data;
    }

    public function saveCart($cart_data)
    {
        $this->Session->set('cart_data', $cart_data);
    }

    public function getCart()
    {
        return empty($this->Session->get('cart_data')) ? $this->initializeCart() : $this->Session->get('cart_data');
    }

    public function applyDiscountCode($code)
    {
        $cart_data = $this->getCart();
        $Discount = $this->Doctrine->getRepository('AppBundle:Discount')->findOneBy(['code'=>$code]);
        
        $cart_data['discount']['amount'] = null;
        $cart_data['discount']['code'] = null;
        $cart_data['discount']['error'] = null;
        
        if(empty($Discount))
        {
            $cart_data['discount']['error'] = 'Invalid promo code.';
        }
        else
        {
            if(
                ($Discount->getType() < 3 && $Discount->getMinimum() <= ($cart_data['total'] * $cart_data['price']))
                || $Discount->getType() == 3 && $Discount->getMinimum() <= ($cart_data['quantity'] - $Discount->getAmount())
            )
            {
                switch($Discount->getType())                  
                {
                    case 3:
                        $cart_data['discount']['amount'] = ($cart_data['price'] * $Discount->getAmount());
                    break;
                    case 2:                
                        $cart_data['discount']['amount'] = ($cart_data['total'] * $cart_data['price']) * $Discount->getAmount();
                    break;
                    case 1:
                        $cart_data['discount']['amount'] = $Discount->getAmount();
                    break;
            
                };
                 
                $cart_data['discount']['code'] = $code;
            }
            else
            {
                $cart_data['discount']['error'] = 'You have not met the minimum requirements for this promo code.';
            }

            $this->saveCart($cart_data);
        }

        return $cart_data;
    }

    public function getPrice($quantity = 0)
    {
        $amount = 1;

        if(!empty($this->pricing))
        {        
            foreach($this->pricing as $price)
            {
                if($quantity >= $price['denomination'])
                {
                    $amount = $price['quantity'];
                }
            }

        }

        return $amount;
    }
}
    


?>
