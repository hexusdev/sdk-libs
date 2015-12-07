<?php

use devcookies\SignatureGenerator;

class SignatureGeneratorTest extends PHPUnit_Framework_TestCase
{
	protected $salt = "some salt";

	/**
	 * @test
	 * @dataProvider signParamsProvider
	 */
	public function paramSet(array $params, $resultSign)
	{
		$signgen = new SignatureGenerator($this->salt);

		$this->assertEquals($resultSign, $signgen->assemble($params));
	}

	/**
	 * @dataProvider signGroupParamsProvider
	 */
	public function testSignGroups(array $params, $resultSign)
	{
		$signgen = new SignatureGenerator($this->salt);

		$this->assertEquals(
			$resultSign,
			$signgen->assemble($params, SignatureGenerator::MAXIMUM_RECURSION_DEPTH_FOR_SUBFORM)
		);
	}

	public function signParamsProvider()
	{
		return array(
			array(
				array(
					'c_param' => 'c',
					'a3_param' => 'a3',
					'b3_param' => 'b3',
					'a2_param' => true,
					'b2_param' => false,
					'a1_param' => 1,
					'b1_param' => 2,
				),
				'4c6f5e5471953977779fee0e1de698859883800f'
//				'a1_param:1;a2_param:1;a3_param:a3;b1_param:2;b2_param:0;b3_param:b3;c_param:c;some salt'
			),
			array(
				array(
					'a_param' => 'value 1',
					'd_param' => 'value_3',
					'c_param' => array(
						'c_sub_param' => 'sub value c3',
						'a_sub_param' => 'sub value a1',
						'b_sub_param' => 'sub value b2',
						'a_sub_param' => true,
						'b_sub_param' => false,
					),
					'b_param' => 'value_2',
				),
				'79d6286011ba043a0c041c91e75d96c060b81357'
//				'a_param:value 1;b_param:value_2;c_param:a_sub_param:1;b_sub_param:0;c_sub_param:sub value c3;d_param:value_3;some salt'
			),
			array( // Two arrays test
				array(
					'a_param' => array(
						'a1_sub_param' => 'sub value a1',
						'a2_sub_param' => 'sub value a2',
					),
					'b_param' => array(
						'b1_sub_param' => 'sub value b1',
						'b2_sub_param' => 'sub value b2',
					),
					'c_param' => 'value c',
				),
				'042b3d7bb3403b8c6e1bc2896862eb6cc91f7d9d'
//				'a_param:a1_sub_param:sub value a1;a2_sub_param:sub value a2;b_param:b1_sub_param:sub value b1;b2_sub_param:sub value b2;c_param:value c;some salt'
			),
			array( // empty value ignoring test
				array(
					'a_param' => 'a_value',
					'b_param' => '',
				),
				'420a46f6bbe215711b0837496fb827355894d549'
//				'a_param:a_value;some salt'
			),
			array(
				array(
					'c_param' => array(
						'c_sub_param' => array(
							'a_sub_sub_param' => 'value 3',
						),
						'a_sub_param' => 'value 2',
					),
					'a_param' => 'value 1',
				),
				'804ea804e9a784a49585d2dbb1ed5d310479099f'
//				'a_param:value 1;c_param:a_sub_param:value 2;some salt'
			),
		);
	}

	public function signGroupParamsProvider()
	{
		return
			array(
				array( // empty value ignoring test
					array(
						'a_param' => 'a_value',
						'b_param' => '',
					),
					'420a46f6bbe215711b0837496fb827355894d549'
//					'a_param:a_value;some salt'
				),
				array( // ordering & conversion & boolean test
					array(
						'C_param' => 'c',
						'a3_param' => 'a3',
						'b3_param' => 'b3',
						'A2_param' => true,
						'B2_param' => false,
						'a1_param' => 1,
						'b1_param' => 2,
					),
					'bfc8bf64378ee29edd0b19510b614ed4d26debcb'
//					'A2_param:1;B2_param:0;C_param:c;a1_param:1;a3_param:a3;b1_param:2;b3_param:b3;some salt'
				),
				array(	// 2 level deep with ordering & boolean test
					array(
						'A_param' => 'value 1',
						'D_param' => 'value_3',
						'C_param' => array(
							'C_sub_param' => 'sub value c3',
							'A_sub_param' => 'sub value a1',
							'B_sub_param' => 'sub value b2',
							'a_sub_param' => true,
							'b_sub_param' => false,
						),
						'B_param' => 'value_2',
					),
					'6f1700b9c01c1b01efdb5c2af84044ef007478f7'
//					'A_param:value 1;B_param:value_2;C_param:A_sub_param:sub value a1;B_sub_param:sub value b2;C_sub_param:sub value c3;a_sub_param:1;b_sub_param:0;D_param:value_3;some salt'
				),
				array(	// 3 level deep test
					array(
						'A_param' => 'value 1',
						'D_param' => 'value_3',
						'C_param' => array(
							'C_sub_param' => 'sub value c3',
							'A_sub_param' => array(
								'A_sub_sub_param' => 'sub sub value',
							),
							'B_sub_param' => 'sub value b2',
							'a_sub_param' => true,
							'b_sub_param' => false,
						),
						'B_param' => 'value_2',
					),
					'5315a73ff51085c07612a95e24612c905e7afb8e'
//					'A_param:value 1;B_param:value_2;C_param:A_sub_param:A_sub_sub_param:sub sub value;B_sub_param:sub value b2;C_sub_param:sub value c3;a_sub_param:1;b_sub_param:0;D_param:value_3;some salt'
				),
				array(	// 4 level deep test
					array(
						'A_param' => 'value 1',
						'D_param' => 'value_3',
						'C_param' => array(
							'C_sub_param' => 'sub value c3',
							'A_sub_param' => array(
								'A_sub_sub_param' => 'sub sub value',
							),
							'B_sub_param' => array(
								'B_sub_sub_param'  => array(
									'B_sub_sub_sub_param' => 'sub sub sub value',
								),
							),
							'a_sub_param' => true,
							'b_sub_param' => false,
						),
						'B_param' => 'value_2',
					),
					'e48bb72d7c667406dbfb77c39e3e9ad04f5f7af6'
//					'A_param:value 1;B_param:value_2;C_param:A_sub_param:A_sub_sub_param:sub sub value;C_sub_param:sub value c3;a_sub_param:1;b_sub_param:0;D_param:value_3;some salt'
				),
			)
		;
	}
}