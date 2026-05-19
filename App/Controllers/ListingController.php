<?php
    namespace App\Controllers;

    use Framework\Database;
    use Framework\Validation;
    use Framework\Session;
    use Framework\Authorization;

    class ListingController {
        protected $db;

        public function __construct() {
            Session::start();
            $config = require basePath('config/db.php');
            $this->db = new Database($config);
        }

        /**
         * Show all listings
         */
        public function index($params = []) {
            $listings = $this->db->Query(
                "SELECT * FROM listings ORDER BY created_at DESC"
            )->fetchAll();

            if (empty($listings)) {
                $this->ensureDemoData();
                $listings = $this->db->Query(
                    "SELECT * FROM listings ORDER BY created_at DESC"
                )->fetchAll();
            }

            loadView('Listings/index', ['listings' => $listings]);
        }

        /**
         * Seed demo data when no listings exist
         */
        protected function ensureDemoData() {
            $demoEmail = 'demo@jobseeker.test';
            $user = $this->db->Query(
                "SELECT * FROM users WHERE email = :email",
                ['email' => $demoEmail]
            )->fetch();

            if (!$user) {
                $this->db->Query(
                    "INSERT INTO users (name, email, city, state, password)
                    VALUES (:name, :email, :city, :state, :password)",
                    [
                        'name' => 'Demo Employer',
                        'email' => $demoEmail,
                        'city' => 'Austin',
                        'state' => 'TX',
                        'password' => password_hash('demo1234', PASSWORD_DEFAULT)
                    ]
                );

                $userId = $this->db->conn->lastInsertId();
            } else {
                $userId = $user->id;
            }

            $this->db->Query(
                "INSERT INTO listings (user_id, title, description, salary, tags, company, address, city, state, phone, email, requirements, benefits)
                VALUES
                (:user_id, :title1, :description1, :salary1, :tags1, :company1, :address1, :city1, :state1, :phone1, :email1, :requirements1, :benefits1),
                (:user_id, :title2, :description2, :salary2, :tags2, :company2, :address2, :city2, :state2, :phone2, :email2, :requirements2, :benefits2)",
                [
                    'user_id' => $userId,
                    'title1' => 'Full Stack Developer',
                    'description1' => 'Build modern web applications using PHP, JavaScript, and cloud APIs.',
                    'salary1' => '95000.00',
                    'tags1' => 'PHP,JavaScript,Remote',
                    'company1' => 'TechWave',
                    'address1' => '123 Main St',
                    'city1' => 'Austin',
                    'state1' => 'TX',
                    'phone1' => '512-555-0101',
                    'email1' => 'jobs@techwave.test',
                    'requirements1' => '3+ years PHP experience, ability to write clean code.',
                    'benefits1' => 'Remote friendly, health insurance, paid time off.',
                    'title2' => 'Product Designer',
                    'description2' => 'Design intuitive user experiences for web and mobile products.',
                    'salary2' => '85000.00',
                    'tags2' => 'Design,UI/UX,Remote',
                    'company2' => 'BrightApps',
                    'address2' => '789 Oak Ave',
                    'city2' => 'Austin',
                    'state2' => 'TX',
                    'phone2' => '512-555-0202',
                    'email2' => 'careers@brightapps.test',
                    'requirements2' => 'Portfolio required, strong communication skills.',
                    'benefits2' => 'Flexible schedule, equity options.'
                ]
            );
        }

        /**
         * Show create listing form
         */
        public function create($params = []) {
            loadView('Listings/create');
        }

        /**
         * Show single listing
         */
        public function show($params = []) {
            $id = $params['id'] ?? null;

            $listing = $this->db->Query(
                "SELECT * FROM listings WHERE id = :id",
                ['id' => $id]
            )->fetch();

            if (!$listing) {
                ErrorController::notFound('Listing not found');
                return;
            }

            loadView('Listings/show', ['listing' => $listing]);
        }

        /**
         * Store listing in database
         */
        public function store($params = []) {
            $allowedFields = [
                'title', 'description', 'salary', 'tags',
                'company', 'address', 'city', 'state',
                'phone', 'email', 'requirements', 'benefits'
            ];

            $newListingData = array_intersect_key(
                $_POST,
                array_flip($allowedFields)
            );

            $sessionUser = Session::get('user');
            if ($sessionUser === null || !isset($sessionUser['id'])) {
                Session::setFlashMessage('error_message', 'You must be logged in to post a job.');
                redirect('/login');
            }

            $newListingData['user_id'] = $sessionUser['id'];

            foreach ($newListingData as $field => $value) {
                if ($value === '') {
                    $newListingData[$field] = null;
                }
            }

            $newListingData = array_map('sanitize', $newListingData);

            $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($newListingData[$field]) ||
                    !Validation::string($newListingData[$field])) {
                    $errors[] = ucfirst($field) . ' is required';
                }
            }

            if (!empty($errors)) {
                loadView('Listings/create', [
                    'errors' => $errors,
                    'listing' => (object) $newListingData
                ]);
            } else {
                $fields = implode(', ', array_keys($newListingData));
                $values = ':' . implode(', :', array_keys($newListingData));

                $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";
                $this->db->Query($query, $newListingData);

                Session::setFlashMessage('success_message', 'Listing created successfully!');
                redirect('/listings');
            }
        }

        /**
         * Show edit listing form
         */
        public function edit($params = []) {
            $id = $params['id'] ?? null;

            $listing = $this->db->Query(
                "SELECT * FROM listings WHERE id = :id",
                ['id' => $id]
            )->fetch();

            if (!$listing) {
                ErrorController::notFound('Listing not found');
                return;
            }

            if (!Authorization::isOwner($listing->user_id)) {
                Session::setFlashMessage('error_message', 'You are not authorized to edit this listing');
                redirect('/listings/' . $id);
                return;
            }

            loadView('Listings/edit', ['listing' => $listing]);
        }

        /**
         * Update listing in database
         */
        public function update($params = []) {
            $id = $params['id'] ?? null;

            $listing = $this->db->Query(
                "SELECT * FROM listings WHERE id = :id",
                ['id' => $id]
            )->fetch();

            if (!$listing) {
                ErrorController::notFound('Listing not found');
                return;
            }

            if (!Authorization::isOwner($listing->user_id)) {
                Session::setFlashMessage('error_message', 'You are not authorized to update this listing');
                redirect('/listings/' . $id);
                return;
            }

            $allowedFields = [
                'title', 'description', 'salary', 'tags',
                'company', 'address', 'city', 'state',
                'phone', 'email', 'requirements', 'benefits'
            ];

            $updatedValues = array_intersect_key(
                $_POST,
                array_flip($allowedFields)
            );

            $updatedValues = array_map('sanitize', $updatedValues);

            $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($updatedValues[$field]) ||
                    !Validation::string($updatedValues[$field])) {
                    $errors[] = ucfirst($field) . ' is required';
                }
            }

            if (!empty($errors)) {
                loadView('Listings/edit', [
                    'listing' => $listing,
                    'errors' => $errors
                ]);
                return;
            }

            $updateFields = [];
            foreach (array_keys($updatedValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }
            $updateFields = implode(', ', $updateFields);
            $updatedValues['id'] = $id;

            $query = "UPDATE listings SET {$updateFields} WHERE id = :id";
            $this->db->Query($query, $updatedValues);

            Session::setFlashMessage('success_message', 'Listing updated successfully!');
            redirect('/listings/' . $id);
        }

        /**
         * Delete a listing
         */
        public function destroy($params = []) {
            $id = $params['id'] ?? null;

            $listing = $this->db->Query(
                "SELECT * FROM listings WHERE id = :id",
                ['id' => $id]
            )->fetch();

            if (!$listing) {
                ErrorController::notFound('Listing not found');
                return;
            }

            if (!Authorization::isOwner($listing->user_id)) {
                Session::setFlashMessage('error_message', 'You are not authorized to delete this listing');
                redirect('/listings/' . $id);
                return;
            }

            $this->db->Query(
                "DELETE FROM listings WHERE id = :id",
                ['id' => $id]
            );

            Session::setFlashMessage('success_message', 'Listing deleted successfully!');
            redirect('/listings');
        }

        /**
         * Search listings by keyword and location
         */
        public function search($params = []) {
            $keywords = isset($_GET['keywords'])
                ? trim($_GET['keywords'])
                : '';

            $location = isset($_GET['location'])
                ? trim($_GET['location'])
                : '';

            $query = "SELECT * FROM listings WHERE
                (title LIKE :keywords
                OR description LIKE :keywords
                OR tags LIKE :keywords
                OR company LIKE :keywords)
                AND (city LIKE :location OR state LIKE :location)
                ORDER BY created_at DESC";

            $params = [
                'keywords' => '%' . $keywords . '%',
                'location' => '%' . $location . '%'
            ];

            $listings = $this->db->Query($query, $params)->fetchAll();

            loadView('Listings/index', [
                'listings' => $listings,
                'keywords' => $keywords,
                'location' => $location
            ]);
        }
    }
?>