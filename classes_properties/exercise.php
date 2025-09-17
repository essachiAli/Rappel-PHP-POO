<?php
declare(strict_types=1);


// Class User

class User {
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $bio = null,
        public int $articlesCount = 0,
    ) {}

    // Method to return initials of the user name
    public function initials(): string {
        $parts = preg_split('/\s+/', trim($this->name));
        $letters = array_map(fn($p) => strtoupper(substr($p, 0, 1)), $parts);
        return implode('', $letters);
    }

    // Method to convert the object into an associative array
    public function toArray(): array {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'bio'           => $this->bio,
            'articlesCount' => $this->articlesCount,
            'initials'      => $this->initials(),
        ];
    }
}


// Factory: UserFactory
class UserFactory {
    public static function fromArray(array $u): User {
        $id    = max(1, (int)($u['id'] ?? 0)); // ensure id >= 1
        $name  = trim((string)($u['name'] ?? 'Inconnu'));
        $email = trim((string)($u['email'] ?? ''));
        if ($email === '') {
            throw new InvalidArgumentException('email requis');
        }
        $bio   = isset($u['bio']) ? (string)$u['bio'] : null;
        $count = (int)($u['articlesCount'] ?? 0);

        return new User($id, $name, $email, $bio, $count);
    }
}


// Input dataset

$authors = [
    ['id'=>1,'name'=>'Amina Zouhair','email'=>'amina@example.com','bio'=>'Laravel fan','articlesCount'=>5],
    ['id'=>2,'name'=>'Yassine Mallouli','email'=>'yassine@example.com','bio'=>null,'articlesCount'=>3],
    ['id'=>3,'name'=>'Fatima Benali','email'=>'fatima@example.com','articlesCount'=>7],
];


// Convert raw data → objects
$users = array_map(
    fn(array $u) => UserFactory::fromArray($u),
    $authors
);


// Display report

foreach ($users as $user) {
    $data = $user->toArray();
    echo "- {$data['name']} ({$data['initials']}) — Articles: {$data['articlesCount']}\n";
}