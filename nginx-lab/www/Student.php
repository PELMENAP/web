<?php
class Student {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function add($name, $email, $age, $faculty, $agree, $studyForm) {
        $sql = "INSERT INTO students (name, email, age, faculty, agree_rules, study_form) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $email, $age, $faculty, $agree, $studyForm]);
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM students ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function updateName($id, $name) {
        $sql = "UPDATE students SET name = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM students WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
?>