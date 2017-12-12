<?php

namespace AppBundle\Services;

class ReportService
{

    public function summarizeReport($report_data)
    {
        if(count($report_data) > 0)
        {         
            $products = [];
            $reviews = [];

            foreach($report_data as $record)
            {
                $category_parts = explode('_', $record['category']);
                $product_id = null;
                if(strlen($category_parts[1]) == 36)
                {
                    $product_id = $category_parts[1];
                    $products[$product_id] = $product_id;
                }
            }

            $product_visits = [];

            foreach($report_data as $record)
            {
                $user = $record['user_id'];
                if(!isset($product_visits[$user])) {
                    $product_visits[$user] = [];
                }

                $category_parts = explode('_', $record['category']);
                $product_id = null;
                $view_index = $category_parts[0];
                if(!isset($product_visits[$user][$view_index]) && $view_index != 'PS') {
                    $product_visits[$user][$view_index] = [];
                }

                if(strlen($category_parts[1]) == 36)
                {
                    $product_id = $category_parts[1];
                    $products[$product_id] = $product_id;
                    if(!isset($product_visits[$user][$view_index][$product_id])) {
                        $product_visits[$user][$view_index][$product_id] = 0;
                    }
                }                

                if(empty($summary_data[$user]))
                {
                    $summary_data[$user] = [
                       'user_external_id' => $record['user_external_id']
                       ,'user_id' => $record['user_id']
                       ,'user_ip' => $record['user_ip']
                       ,'configuration_id' => $record['configuration_id']
                       ,'configuration_settings' => $record['configuration_settings'] 
                       ,'session_id' => $record['session_id']
                       ,'Shopping_Abandoned (0=No, 1=Yes)' => 0
                       ,'total_time_spent_on_website' => 0
                       ,'total_time_spent_on_Instructions_pages' => 0
                       ,'total_visits_to_SRS_pages' => 0
                       ,'total_time_spent_on_SRS_pages' => 0
                       ,'total_visits_to_PIS_pages' => 0
                       ,'total_time_spent_on_PIS_pages' => 0
                       ,'total_visits_to_CRS_pages' => 0
                       ,'total_time_spent_on_CRS_pages' => 0
                       ,'total_visits_to_PS_pages' => 0
                       ,'total_time_spent_on_PS_pages' => 0
                       ,'total_visits_to_Instructions_pages' => 0
                    ];
                    
                    //@TODO This is meaningless foreach under another foreach where $products array 
                    //still generating and will run again and again the same data.
                    foreach($products as $product_id)
                    {
                        $summary_data[$user]["Product_$product_id" ."_total_visits_to_PIS_pages"] = 0;
                        $summary_data[$user]["Product_$product_id" ."_total_time_spent_on_PIS_pages"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_total_visits_to_CRS_pages"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_total_time_spent_on_CRS_pages"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_Of_Reviews_Seen"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_Of_Unique_Reviews_Seen"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateDescending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateAscending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingDescending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingAscending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessDescending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessAscending_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStar_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy5Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy4Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy3Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy2Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy1Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_added_to_cart (0=No, 1=Yes)"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_purchased (0=No, 1=Yes)"] = 0;
                    }
                }
                //@TODO No idea why this snipet code wrote again and again!!
                $category_parts = explode('_', $record['category']);
                $product_id = null;
                
                if(strlen($category_parts[1]) == 36)
                {
                    $product_id = $category_parts[1];
                }   
                
                if($record['event'] == 'duration')
                {
                    $time_string = null;
                    $visit_string = null;
                    $visit = str_replace('Visit: ', '', $record['label']);

                    if(stristr($record['category'], 'Instructions_') > -1)
                    {
                        //Visiting string was missing previously.
                        $visit_string = 'total_visits_to_Instructions_pages';
                        $time_string = 'total_time_spent_on_Instructions_pages';
                    }

                    if(stristr($record['category'], 'SRS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_SRS_pages';
                        $visit_string = 'total_visits_to_SRS_pages';
                    }

                    if(stristr($record['category'], 'PIS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_PIS_pages';
                        $visit_string = 'total_visits_to_PIS_pages';
                    }

                    if(stristr($record['category'], 'CRS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_CRS_pages';
                        $visit_string = 'total_visits_to_CRS_pages';
                    }

                    if(stristr($record['category'], 'PS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_PS_pages';
                        $visit_string = 'total_visits_to_PS_pages';
                    }

                    if(!empty($time_string))
					{			
                        $summary_data[$user][$time_string] += $record['time'];

                        if (!empty($product_id))
                            $summary_data[$user]['Product_' . $product_id . '_' . $time_string] += $record['time'];
                    }

                    if(!empty($visit_string))
                    {
                        //@TODO
                        // Why there have no incremental statement for "total_visits_to_SRS_pages" ?
                        //*push
                        if($visit_string=='total_visits_to_SRS_pages' || $visit_string=='total_visits_to_PS_pages')
                        { 
                            if($summary_data[$user][$visit_string] < $visit )
                            {
                                $summary_data[$user][$visit_string] = $visit;
                            }
                        }
                        else
                        {
                             $summary_data[$user][$visit_string] += $visit;
                        }
                       //push*
                        //
//                        $summary_data[$user][$visit_string] += $visit;
                    }

                    $summary_data[$user]['total_time_spent_on_website'] += $record['time'];

                    if(!empty($visit_string) && !empty($product_id) && $visit > $summary_data[$user]['Product_' . $product_id . '_' . $visit_string])
                        $summary_data[$user]['Product_' . $product_id . '_' . $visit_string] = $visit;

                    if(!empty($product_id) && !empty($view_index) && $visit > $product_visits[$user][$view_index][$product_id]) {
                            $product_visits[$user][$view_index][$product_id] = $visit;
                    }

                }

                if($record['event'] == 'seen')
                {
                    $review_id = $category_parts[2];
                    if(empty($reviews[$review_id]))
                        $reviews[$review_id] = 0;

                    $reviews[$review_id]++;
                    $summary_data[$user]["Product_$product_id" . "_Number_Of_Reviews_Seen"] = array_sum($reviews);
                    $summary_data[$user]["Product_$product_id" . "_Number_Of_Unique_Reviews_Seen"] = count($reviews);
                }
        

                if($record['event'] == 'click')
                {
					if(stristr($record['category'], '_Purchase') > -1)				                   
                       	$summary_data[$user]['Product_' . $product_id . '_purchased (0=No, 1=Yes)'] = 1;                    		

                    if(stristr($record['category'], 'Add_Cart') > -1)
                        $summary_data[$user]["Product_$product_id" . "_added_to_cart (0=No, 1=Yes)"] = 1;

                    if(stristr($record['category'], 'Abandon_') > -1)
                        $summary_data[$user]['Shopping_Abandoned (0=No, 1=Yes)'] = 1;

                    if(stristr($record['category'], 'SortByDateDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateDescending_clicked"]++;


                    if(stristr($record['category'], 'SortByDateAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateAscending_clicked"]++;

                    if(stristr($record['category'], 'SortByRatingDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingDescending_clicked"]++;

                    if(stristr($record['category'], 'SortByRatingAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingAscending_clicked"]++;

                    if(stristr($record['category'], 'SortByHelpfulnessDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessDescending_clicked"]++;

                    if(stristr($record['category'], 'SortByHelpfulnessAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessAscending_clicked"]++;

                    if(stristr($record['category'], 'FilterByAllStar') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStar_clicked"]++;

                    if(stristr($record['category'], 'FilterBy5Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy5Star_clicked"]++;
    
                    if(stristr($record['category'], 'FilterBy4Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy4Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy3Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy3Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy2Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy2Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy1Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy1Star_clicked"]++;

                }
            }
        }

        $total_visits = [];
//print_r($reviews); exit;
        if(!empty($product_visits)) {
            foreach($product_visits as $user=>$views) {
                if(!isset($total_visits[$user])) {
                    $total_visits[$user] = [];
                }
                foreach($views as $view_index=>$products) {
                    if(!isset($total_visits[$user][$view_index])) {
                        $total_visits[$user][$view_index] = 0;
                    }

                    foreach($products as $product_id=>$tally) {
                        $total_visits[$user][$view_index] += $tally;
                    }
                }
            }
        }

        foreach($total_visits as $user=>$views) {
            foreach($views as $view_index=>$total) {
                //Do not replace SRS and Instructions page view
                if($view_index != 'SRS' && $view_index != 'Instructions' && $view_index != 'PS' && $view_index != 'Product'){
                    $summary_data[$user]['total_visits_to_' . $view_index . '_pages'] = $total;
                }
            }
        }

        $records = [];

        if(!empty($summary_data))
        { 
            foreach($summary_data as $record)
            {
                $records[] = $record;
            }
        }
        return $records;
    }
    
    public function summarizeReportTest($report_data)
    {
        if(count($report_data) > 0)
        {         
            $products = [];
            $reviews = [];
            $product_visits = [];
            $summary_data = [];

            foreach($report_data as $record)
            {
                $category_parts = explode('_', $record['category']);
                $product_id = null;
                if(strlen($category_parts[1]) == 36)
                {
                    $user = $record['user_id'];
                    $view_index = $category_parts[0];
                    $product_id = $category_parts[1];
                    if(!isset($productList[$user][$product_id])){
                        $productList[$user][$product_id] = $product_id;
                    }
                    
                    ///////***********************/////////////
                    
                
                if(!isset($product_visits[$user])) {
                    $product_visits[$user] = [];
                }
                
                if(!isset($product_visits[$user][$view_index]) && $view_index != 'PS') {
                    $product_visits[$user][$view_index] = [];
                }

                if(!isset($product_visits[$user][$view_index][$product_id])) {
                    $product_visits[$user][$view_index][$product_id] = 0;
                }
                
                
                if(!isset($summary_data[$user]) || empty($summary_data[$user]))
                {
                    $summary_data[$user] = [
                       'user_external_id' => $record['user_external_id']
                       ,'user_id' => $record['user_id']
                       ,'user_ip' => $record['user_ip']
                       ,'configuration_id' => $record['configuration_id']
                       ,'configuration_settings' => $record['configuration_settings'] 
                       ,'session_id' => $record['session_id']
                       ,'Shopping_Abandoned (0=No, 1=Yes)' => 0
                       ,'total_time_spent_on_website' => 0
                       ,'total_time_spent_on_Instructions_pages' => 0
                       ,'total_visits_to_SRS_pages' => 0
                       ,'total_time_spent_on_SRS_pages' => 0
                       ,'total_visits_to_PIS_pages' => 0
                       ,'total_time_spent_on_PIS_pages' => 0
                       ,'total_visits_to_CRS_pages' => 0
                       ,'total_time_spent_on_CRS_pages' => 0
                       ,'total_visits_to_PS_pages' => 0
                       ,'total_time_spent_on_PS_pages' => 0
                       ,'total_visits_to_Instructions_pages' => 0
                    ];                    
                    ///////*************************/////////////////// 
                } 
                    
                }
            }
//print_r($productList); exit; 
            foreach($productList as $user => $product)
            {
                foreach($product as $key => $product_id){
                $summary_data[$user]["Product_$product_id" ."_total_visits_to_PIS_pages"] = 0;
                $summary_data[$user]["Product_$product_id" ."_total_time_spent_on_PIS_pages"] = 0;
                $summary_data[$user]["Product_$product_id" . "_total_visits_to_CRS_pages"] = 0;
                $summary_data[$user]["Product_$product_id" . "_total_time_spent_on_CRS_pages"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_Of_Reviews_Seen"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_Of_Unique_Reviews_Seen"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateDescending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateAscending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingDescending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingAscending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessDescending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessAscending_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStar_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy5Star_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy4Star_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy3Star_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy2Star_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy1Star_clicked"] = 0;
                $summary_data[$user]["Product_$product_id" . "_added_to_cart (0=No, 1=Yes)"] = 0;
                $summary_data[$user]["Product_$product_id" . "_purchased (0=No, 1=Yes)"] = 0;
                }
            }
            
            foreach($report_data as $record)
            {
                $category_parts = explode('_', $record['category']);
                $product_id = null;
                $user = $record['user_id'];
                
                if(strlen($category_parts[1]) == 36)
                {
                    $product_id = $category_parts[1];
                }                  
                
                if($record['event'] == 'duration')
                {
                    $time_string = null;
                    $visit_string = null;
                    $visit = str_replace('Visit: ', '', $record['label']);

                    if(stristr($record['category'], 'Instructions_') > -1)
                    {
                        //Visiting string was missing previously.
                        $visit_string = 'total_visits_to_Instructions_pages';
                        $time_string = 'total_time_spent_on_Instructions_pages';
                    }

                    if(stristr($record['category'], 'SRS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_SRS_pages';
                        $visit_string = 'total_visits_to_SRS_pages';
                    }

                    if(stristr($record['category'], 'PIS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_PIS_pages';
                        $visit_string = 'total_visits_to_PIS_pages';
                    }

                    if(stristr($record['category'], 'CRS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_CRS_pages';
                        $visit_string = 'total_visits_to_CRS_pages';
                    }

                    if(stristr($record['category'], 'PS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_PS_pages';
                        $visit_string = 'total_visits_to_PS_pages';
                    }

                    if(!empty($time_string))
					{			
                        $summary_data[$user][$time_string] += $record['time'];

                        if (!empty($product_id))
                            $summary_data[$user]['Product_' . $product_id . '_' . $time_string] += $record['time'];
                    }

                    if(!empty($visit_string))
                    {  
                        //*push
                        if($visit_string=='total_visits_to_SRS_pages' || $visit_string=='total_visits_to_PS_pages')
                        { 
                            if($summary_data[$user][$visit_string] < $visit )
                            {
                                $summary_data[$user][$visit_string] = $visit;
                            }
                        }
                        else
                        {
                             $summary_data[$user][$visit_string] += $visit;
                        }
                       //push*
                        //
//                        $summary_data[$user][$visit_string] += $visit;
                    }

                    $summary_data[$user]['total_time_spent_on_website'] += $record['time'];

                    if(!empty($visit_string) && !empty($product_id) && $visit > $summary_data[$user]['Product_' . $product_id . '_' . $visit_string])
                        $summary_data[$user]['Product_' . $product_id . '_' . $visit_string] = $visit;

                    if(!empty($product_id) && !empty($view_index) && isset($product_visits[$user][$view_index][$product_id]) && $visit > $product_visits[$user][$view_index][$product_id]) {
                            $product_visits[$user][$view_index][$product_id] = $visit;
                    }

                }

                if($record['event'] == 'seen')
                {
                    $review_id = $category_parts[2];
                    if(empty($reviews[$review_id]))
                        $reviews[$review_id] = 0;

                    $reviews[$review_id]++;
                    $summary_data[$user]["Product_$product_id" . "_Number_Of_Reviews_Seen"] = array_sum($reviews);
                    $summary_data[$user]["Product_$product_id" . "_Number_Of_Unique_Reviews_Seen"] = count($reviews);
                }
        

                if($record['event'] == 'click')
                {
                    if(stristr($record['category'], '_Purchase') > -1)				                   
                       	$summary_data[$user]['Product_' . $product_id . '_purchased (0=No, 1=Yes)'] = 1;                    		

                    if(stristr($record['category'], 'Add_Cart') > -1)
                        $summary_data[$user]["Product_$product_id" . "_added_to_cart (0=No, 1=Yes)"] = 1;

                    if(stristr($record['category'], 'Abandon_') > -1)
                        $summary_data[$user]['Shopping_Abandoned (0=No, 1=Yes)'] = 1;

                    if(stristr($record['category'], 'SortByDateDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateDescending_clicked"]++;


                    if(stristr($record['category'], 'SortByDateAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByDateAscending_clicked"]++;

                    if(stristr($record['category'], 'SortByRatingDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingDescending_clicked"]++;

                    if(stristr($record['category'], 'SortByRatingAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByRatingAscending_clicked"]++;

                    if(stristr($record['category'], 'SortByHelpfulnessDescending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessDescending_clicked"]++;

                    if(stristr($record['category'], 'SortByHelpfulnessAscending') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_SortByHelpfulnessAscending_clicked"]++;

                    if(stristr($record['category'], 'FilterByAllStar') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStar_clicked"]++;

                    if(stristr($record['category'], 'FilterBy5Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy5Star_clicked"]++;
    
                    if(stristr($record['category'], 'FilterBy4Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy4Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy3Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy3Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy2Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy2Star_clicked"]++;

                    if(stristr($record['category'], 'FilterBy1Star') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy1Star_clicked"]++;

                }
            }
        }

        $total_visits = [];
        if(!empty($product_visits)) {
            foreach($product_visits as $user=>$views) {
                if(!isset($total_visits[$user])) {
                    $total_visits[$user] = [];
                }
                foreach($views as $view_index=>$products) {
                    if(!isset($total_visits[$user][$view_index])) {
                        $total_visits[$user][$view_index] = 0;
                    }
                    
                    $total_visits[$user][$view_index] = array_sum($products);                    
                }
            }
        }

        foreach($total_visits as $user=>$views) {
            foreach($views as $view_index=>$total) {
                //Do not replace SRS and Instructions page view
                if($view_index != 'SRS' && $view_index != 'Instructions' && $view_index != 'PS' && $view_index != 'Product'){
                    $summary_data[$user]['total_visits_to_' . $view_index . '_pages'] = $total;
                }
            }
        }

        $records = [];

        if(!empty($summary_data))
        { 
            foreach($summary_data as $record)
            {
                $records[] = $record;
            }
        }
        return $records;
    }

}
