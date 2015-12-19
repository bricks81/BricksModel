<?php

return array(
	'service_manager' => array(
		'factories' => array(
			'BricksModel' => 'Bricks\Model\Model',			
		),
	),	
	'BricksConfig' => array(
		'__DEFAULT_NAMESPACE__' => array(
			'BricksClassLoader' => array(
				'BricksModel' => array(
					'aliasMap' => array(
						'modelClass' => 'Bricks\Model\Model',
					),
				),
			),
		),		
	),
);