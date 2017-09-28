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

            foreach($report_data as $record)
            {
                $user = $record['user_id'];
 
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
                       ,'total_time_spent_on_instructions_page' => 0
                       ,'total_visits_to_SRS_page' => 0
                       ,'total_time_spent_on_SRS_page' => 0
                       ,'total_visits_to_PIS_pages' => 0
                       ,'total_time_spent_on_PIS_pages' => 0
                       ,'total_visits_to_CRS_pages' => 0
                       ,'total_time_spent_on_CRS_pages' => 0
                       ,'total_visits_to_PS_page' => 0
                       ,'total_time_spent_on_PS_page' => 0
                    ];

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
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStars_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy5Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy4Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy3Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy2Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterBy1Star_clicked"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_added_to_cart (0=No, 1=Yes)"] = 0;
                        $summary_data[$user]["Product_$product_id" . "_purchased (0=No, 1=Yes)"] = 0;
                    }
                }

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
                        $time_string = 'total_time_spent_on_instructions_page';
                    }

                    if(stristr($record['category'], 'SRS_') > -1)
                    {
                        $time_string = 'total_time_spent_on_SRS_page';
                        $visit_string = 'total_visits_to_SRS_page';
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
                        $time_string = 'total_time_spent_on_PS_page';
                        $visit_string = 'total_visits_to_PS_page';
                    }

                    if(!empty($time_string))
					{			
                        $summary_data[$user][$time_string] += $record['time'];

                   		if(!empty($product_id))
							$summary_data[$user]['Product_' . $product_id . '_' . $time_string] += $record['time'];
					}
 
                    if(!empty($visit_string))
					{
                        $summary_data[$user][$visit_string] += $visit;
					}

                    $summary_data[$user]['total_time_spent_on_website'] += $record['time'];

                    if(!empty($visit_string) && !empty($product_id) && $visit > $summary_data[$user]['Product_' . $product_id . '_' . $visit_string])
                        $summary_data[$user]['Product_' . $product_id . '_' . $visit_string] = $visit;

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

                    if(stristr($record['category'], 'FilterByAllStars') > -1)
                        $summary_data[$user]["Product_$product_id" . "_Number_of_Times_FilterByAllStars_clicked"]++;

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
