<?php
/**
 ** @author Grégoire Etot
 ** @date 2011
 * based on the k-means algorithm
 * given an array, it calculates three ranges of values including all the array values
 * @param array $aArray
 * @param int $iNbIterations, must be high when the given array is big (10 by default, should be OK all the time)
 * @param bool $bDontCheckSize to avoid infinite iterations : if the circles size is not enough we recall the function, but this time without verification
 * @return array of 3 arrays(low_value, up_value);
 */
function getClassificationRangesValues($aArray, $bDontCheckSize = false)
{
    $iDiffCirclesSize = 3;
    $iNbIterations = 10;

    // TODO remove the extrem values of $aArray
    $aArray = array_filter($aArray, function($iValue){return ($iValue > 0);}); // we remove the zero values
    $aArray = array_values($aArray);

    // we take three random points (the first ones), but we want them to be sorted so that the circles will be in the good order
    $aStartPoints = array($aArray[0]);

    $i = 0;
    while(isset($aArray[$i]) && $aArray[$i] && count($aStartPoints) <= 2)
    {
        if(in_array($aArray[$i], $aStartPoints))
        {
            $i++;
            continue;
        }
        $aStartPoints[] = $aArray[$i];
        $i++;
    }

    if(count($aStartPoints) <= 2)
    {
        // not enought different points
        return array(0, 0);
    }

    sort($aStartPoints);
    $aCircle1 = array($aStartPoints[0]);
    $aCircle2 = array($aStartPoints[1]);
    $aCircle3 = array($aStartPoints[2]);

    // for each iteration, we take the barycentre (in our case, the average value) of each circle as centers
    // then we put each point in the circle whose center is the nearest
    for($iStep = 1; $iStep <= $iNbIterations; $iStep++)
    {
        $iCenter1 = Utils::getArrayAverage($aCircle1);
        $iCenter2 = Utils::getArrayAverage($aCircle2);
        $iCenter3 = Utils::getArrayAverage($aCircle3);

        $aCircle1 = array();
        $aCircle2 = array();
        $aCircle3 = array();

        foreach($aArray as $iPoint)
        {
            // trouver le point le plus proche
            if(abs($iPoint-$iCenter1) <= abs($iPoint-$iCenter2) && abs($iPoint-$iCenter1) <= abs($iPoint-$iCenter3))
            {
                // $iCenter1 est le plus proche
                $aCircle1[] = $iPoint;
            }
            elseif(abs($iPoint-$iCenter2) <= abs($iPoint-$iCenter3))
            {
                // $iCenter2 est le plus proche
                $aCircle2[] = $iPoint;
            }
            else
            {
                // $iCenter3 est le plus proche
                $aCircle3[] = $iPoint;
            }
        }
    }

    if(!$bDontCheckSize)
    {
        if(((count($aCircle3) * $iDiffCirclesSize) < count($aCircle1)) || ((count($aCircle3) * $iDiffCirclesSize) < count($aCircle2)))
        {
            return getClassificationRangesValues(array_merge($aCircle1, $aCircle2), true);
        }
        elseif(((count($aCircle1) * $iDiffCirclesSize) < count($aCircle3)) || ((count($aCircle1) * $iDiffCirclesSize) < count($aCircle2)))
        {
            return getClassificationRangesValues(array_merge($aCircle2, $aCircle3), true);
        }
    }

    if(empty($aCircle1) || empty($aCircle2) || empty($aCircle3))
    {
        return array(0, 0);
    }

    $iValue1 = (min($aCircle2) + max($aCircle1)) / 2;
    $iValue2 = (min($aCircle3) + max($aCircle2)) / 2;

    $iRoundedValue1 = Utils::getRounded($iValue1);
    $iRoundedValue2 = Utils::getRounded($iValue2);

    return array($iRoundedValue1, $iRoundedValue2);
}