<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 7/22/13
 * Time: 10:58 PM
 * To change this template use File | Settings | File Templates.
 */

	foreach (glob("classes/*.php") as $filename)
	{
		require_once($filename);
	}

	class pCharter{

		private $pChart;

		function __construct(Question $question){
			$this->pChart = new pData();

			$questionData = [];
			$questionLabels=[];

			$counter="A";
			foreach($question->aAnswers as $ans){
				array_push($questionData,$ans->iUsersAnswered);
				array_push($questionLabels,$counter);
				$counter++;
			}

			$this->pChart->addPoints($questionData,"Responses");
			$this->pChart->setSerieTicks("Responses",5);
			$this->pChart->setAxisName(0,"# Responses");
			$this->pChart->addPoints($questionLabels,"Labels");
			$this->pChart->setSerieDescription("# Responses","Questions");
			$this->pChart->setAbscissa("Labels");

			/* Create the pChart object */
			$myPicture = new pImage(700,230,$this->pChart);

			/* Turn of Antialiasing */
			$myPicture->Antialias = FALSE;

			/* Add a border to the picture */
			$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

			/* Set the default font */
			$myPicture->setFontProperties(array("FontName"=>"/var/www/ha/AIPMHRA/php/classes/fonts/pf_arma_five.ttf","FontSize"=>10));

			/* Define the chart area */
			$myPicture->setGraphArea(60,40,650,200);

			/* Draw the scale */
			$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
			$myPicture->drawScale($scaleSettings);

			/* Write the chart legend */
			$myPicture->drawLegend(580,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

			/* Turn on shadow computing */
			$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

			/* Draw the chart */
			$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$settings = array("Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);
			$myPicture->drawBarChart();

			/* Render the picture (choose the best way) */
			$myPicture->render("/var/www/ha/AIPMHRA/img/statGraphs/{$question->idQuestionID}.png");

		}
	}