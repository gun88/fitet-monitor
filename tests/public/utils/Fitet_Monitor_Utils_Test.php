<?php

require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';


class Fitet_Monitor_Utils_Test extends Fitet_Monitor_Test_Case {

	/** @test */
	public function emptyChampionships_test() {
		$this->assertChampionshipsEqual([], Fitet_Monitor_Utils::merge_championships([], []));
		$championships = [
			[
				'seasonId' => 1,
				'championshipId' => 2,
				'content' => 'foobar',
			]
		];

		$this->assertChampionshipsEqual($championships, Fitet_Monitor_Utils::merge_championships($championships, []));
		$this->assertChampionshipsEqual($championships, Fitet_Monitor_Utils::merge_championships([], $championships));
		$this->assertChampionshipsEqual($championships, Fitet_Monitor_Utils::merge_championships($championships, $championships));

	}

	/** @test */
	public function onUnion_test() {
		$this->assertChampionshipsEqual([], Fitet_Monitor_Utils::merge_championships([], []));
		$championships_1 = [['seasonId' => 1, 'championshipId' => 2, 'content' => 'c1']];
		$championships_2 = [['seasonId' => 3, 'championshipId' => 4, 'content' => 'c2']];
		$expected = [['seasonId' => 3, 'championshipId' => 4, 'content' => 'c2'], ['seasonId' => 1, 'championshipId' => 2, 'content' => 'c1']];


		$this->assertChampionshipsEqual($expected, Fitet_Monitor_Utils::merge_championships($championships_1, $championships_2));
	}

	/** @test */
	public function onIntersection_test() {
		$this->assertChampionshipsEqual([], Fitet_Monitor_Utils::merge_championships([], []));
		$championships_1 = [['seasonId' => 1, 'championshipId' => 2, 'content' => 'c1'], ['seasonId' => 3, 'championshipId' => 6, 'content' => 'cInter1']];
		$championships_2 = [['seasonId' => 3, 'championshipId' => 4, 'content' => 'c2'], ['seasonId' => 3, 'championshipId' => 6, 'content' => 'cInter2']];
		$expected = [['seasonId' => 3, 'championshipId' => 6, 'content' => 'cInter2'], ['seasonId' => 3, 'championshipId' => 4, 'content' => 'c2'], ['seasonId' => 1, 'championshipId' => 2, 'content' => 'c1']];


		$this->assertChampionshipsEqual($expected, Fitet_Monitor_Utils::merge_championships($championships_1, $championships_2));
	}

	private static function assertChampionshipsEqual($expected, $actual) {
		$expected_size = count($expected);
		$actual_size = count($actual);
		static::assertEquals($expected_size, $actual_size, "Different size - $expected_size vs. $actual_size");

		for ($i = 0; $i < $actual_size; $i++) {
			self::assertChampionshipEqual($expected[$i], $actual[$i]);
		}

	}

	private static function assertChampionshipEqual($expected, $actual) {
		$expected_season_id = $expected['seasonId'];
		$actual_season_id = $actual['seasonId'];
		static::assertEquals($expected_season_id, $actual_season_id, "Different seasonId - $expected_season_id vs. $actual_season_id");

		$expected_championship_id = $expected['championshipId'];
		$actual_championship_id = $actual['championshipId'];
		static::assertEquals($expected_championship_id, $actual_championship_id, "Different championshipId - $expected_championship_id vs. $actual_championship_id");

		$expected_content = $expected['content'];
		$actual_content = $actual['content'];
		static::assertEquals($expected_content, $actual_content, "Different content - $expected_content vs. $actual_content");

	}


}
