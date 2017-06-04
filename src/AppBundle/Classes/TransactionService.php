<?php

namespace AppBundle\Classes;
use AppBundle\Entity\Machine;
use AppBundle\Entity\Transaction;

class TransactionService
{
    protected $Doctrine = null;
    protected $EntityManager = null;
    protected $Session = null;
    protected $Machine = null;
    protected $Transaction = null;

    function __construct($Doctrine, $Session)
    {  
        $this->Doctrine = $Doctrine;
        $this->Session = $Session;
        $this->EntityManager = $Doctrine->getManager();
    }

    public function setMachine(Machine $Machine)
    {
        $this->Machine = $Machine;
        $this->Session->set('Machine', $Machine->getId());
    }

    public function setMachineById($machine_id)
    {        
        $Machine = $this->Doctrine->getRepository('AppBundle:Machine')->find($machine_id);
        if(empty($Machine))
            throw new \Exception('Invalid machine id');

        $this->setMachine($Machine);
    }

    public function getMachine()
    {
        if($this->Machine == null)
        {         
            $machine_id = $this->Session->get('Machine');
            if(!empty($machine_id))
                $Machine = $this->Doctrine->getRepository('AppBundle:Transaction')->find($machine_id);
        }

        if(empty($this->Machine))
            throw new \Exception('Machine not set');

        return $this->Machine;
    }

    public function getCurrentTransaction($generate = true)
    {
        $Transaction = null;

        if(empty($this->Transaction))
        {
            $transaction_id = $this->Session->get('Transaction');
            if(!empty($transaction_id))    
                $Transaction = $this->Doctrine->getRepository('AppBundle:Transaction')->find($transaction_id);
              
            if(empty($Transaction) && $generate)
            {
                if(empty($this->getMachine()))
                    throw new \Exception('Machine not set');

                $Transaction = $this->Doctrine->getRepository('AppBundle:Transaction')->findCurrentTransaction($this->getMachine()->getId());

                if(empty($Transaction))
                {
                    $Transaction = new Transaction($this->getMachine());
                    $EntityManager = $this->Doctrine->getManager();
                    $EntityManager->persist($Transaction);
                    $EntityManager->flush();
                }

            }           

            $this->setTransaction($Transaction);
        }
        
        return $this->Transaction;
    }

    public function getTotalInserted()
    {
        $tally = 0;
        $Transaction = $this->getCurrentTransaction();
        $bills = $Transaction->getBillsInserted();
        if(count($bills) > 0)
        {
            foreach($bills as $bill)
            {
                $tally += ($bill['quantity'] * $bill['denominaton']);
            }
        }

        return $tally;
    }

    public function isTransactionPaid()
    {
        $CurrentTransaction = $this->getCurrentTransaction();
        if(empty($CurrentTransaction))
            return false;

        return $CurrentTransaction->getStatus() == Transaction::STATUS_PAID;
    }

    public function markDispensingChange()
    {
        $CurrentTransaction = $this->getCurrentTransaction(false);
        if(empty($CurrentTransaction))
           throw new \Exception('No transaction found.');
    
        $CurrentTransaction->setStatus(Transaction::STATUS_DISPENSING_CHANGE);
        $this->EntityManager->persist($CurrentTransaction);
        $this->EntityManager->flush();
        $this->setTransaction($CurrentTransaction);
   
        return $this->Transaction;
    }



    public function markPaid()
    {
        $CurrentTransaction = $this->getCurrentTransaction(false);
        if(empty($CurrentTransaction))
           throw new \Exception('No transaction found.');
    
        $CurrentTransaction->setStatus(Transaction::STATUS_PAID);
        $this->EntityManager->persist($CurrentTransaction);
        $this->EntityManager->flush();
        $this->setTransaction($CurrentTransaction);
   
        return $this->Transaction;
    }


    public function markPrinted()
    {
        $CurrentTransaction = $this->getCurrentTransaction(false);
        if(empty($CurrentTransaction))
           throw new \Exception('No transaction found.');
    
        $CurrentTransaction->setStatus(Transaction::STATUS_PRINTED);
        $this->EntityManager->persist($CurrentTransaction);
        $this->EntityManager->flush();
        $this->setTransaction($CurrentTransaction);
   
        return $this->Transaction;
    }


    public function setTransaction($Transaction)
    {
        $this->Session->set('Transaction', empty($Transaction) ? null : $Transaction->getId());
        $this->Transaction = $Transaction;
    }


    public function resetTransaction()
    {
        $this->Session->set('Transaction', null);
        $this->Transaction = null;
    }

    public function abandonTransaction()
    {
        $Transaction = $this->getCurrentTransaction(false);

        if(!empty($Transaction) && $Transaction->getStatus() == Transaction::STATUS_PENDING)
        {
            $Transaction->setStatus(Transaction::STATUS_ABANDONED);     
            $this->EntityManager->persist($Transaction);
            $this->EntityManager->flush();   
        }
     
        $this->setTransaction(null);
    }

    public function updateTotal($total)
    {
        $Transaction = $this->getCurrentTransaction();
        if(!empty($Transaction))
        {
            $Transaction->setTotal($total);
            $this->setTransaction($Transaction);
            $this->EntityManager->persist($Transaction);
            $this->EntityManager->flush();
        }
    }

    public function setCartData($cart_data)
    {
        $Transaction = $this->getCurrentTransaction();
        if(!empty($Transaction))
        {
            $Transaction->setCartData($cart_data);
            $this->setTransaction($Transaction);
            $this->EntityManager->persist($Transaction);
            $this->EntityManager->flush();
        }
    }

}



?>
