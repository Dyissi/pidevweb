<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Nutritionplan
{
    #[ORM\Id]
    #[ORM\Column(name: "nutrition_id", type: "integer")]
    private int $nutrition_id;

    #[ORM\Column(name: "user_id", type: "integer")]
    private int $user_id;

    #[ORM\Column(name: "nutrition_dietType", type: "string")]
    private string $nutrition_dietType;

    #[ORM\Column(name: "nutrition_allergies", type: "string")]
    private string $nutrition_allergies;

    #[ORM\Column(name: "nutrition_calorie_intake", type: "integer")]
    private int $nutrition_calorie_intake;

    #[ORM\Column(name: "nutrition_start_date", type: "date")]
    private \DateTimeInterface $nutrition_start_date;

    #[ORM\Column(name: "nutrition_end_date", type: "date")]
    private \DateTimeInterface $nutrition_end_date;

    #[ORM\Column(name: "nutrition_meal_plan", type: "text")]
    private string $nutrition_meal_plan;

    #[ORM\Column(name: "nutrition_notes", type: "text")]
    private string $nutrition_notes;

    public function getNutrition_id()
    {
        return $this->nutrition_id;
    }

    public function setNutrition_id($value)
    {
        $this->nutrition_id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getNutrition_dietType()
    {
        return $this->nutrition_dietType;
    }

    public function setNutrition_dietType($value)
    {
        $this->nutrition_dietType = $value;
    }

    public function getNutrition_allergies()
    {
        return $this->nutrition_allergies;
    }

    public function setNutrition_allergies($value)
    {
        $this->nutrition_allergies = $value;
    }

    public function getNutrition_calorie_intake()
    {
        return $this->nutrition_calorie_intake;
    }

    public function setNutrition_calorie_intake($value)
    {
        $this->nutrition_calorie_intake = $value;
    }

    public function getNutrition_start_date()
    {
        return $this->nutrition_start_date;
    }

    public function setNutrition_start_date($value)
    {
        $this->nutrition_start_date = $value;
    }

    public function getNutrition_end_date()
    {
        return $this->nutrition_end_date;
    }

    public function setNutrition_end_date($value)
    {
        $this->nutrition_end_date = $value;
    }

    public function getNutrition_meal_plan()
    {
        return $this->nutrition_meal_plan;
    }

    public function setNutrition_meal_plan($value)
    {
        $this->nutrition_meal_plan = $value;
    }

    public function getNutrition_notes()
    {
        return $this->nutrition_notes;
    }

    public function setNutrition_notes($value)
    {
        $this->nutrition_notes = $value;
    }
}