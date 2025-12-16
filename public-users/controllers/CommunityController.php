<?php
class CommunityController {
	private $file;
	public function __construct() {
		$this->file = __DIR__ . '/../storage/community_stories.json';
		if (!file_exists($this->file)) {
			file_put_contents($this->file, json_encode([]));
		}
	}

	public function addStory($userId, $story) {
		$stories = $this->getStories();
		$stories[] = [
			'user_id' => $userId,
			'story' => $story,
			'date' => date('Y-m-d H:i:s')
		];
		return file_put_contents($this->file, json_encode($stories, JSON_PRETTY_PRINT)) !== false;
	}

	public function getStories() {
		$data = file_get_contents($this->file);
		return $data ? json_decode($data, true) : [];
	}
}
