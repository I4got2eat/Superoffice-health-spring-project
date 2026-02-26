<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-users',
    description: 'Imports users from employees.csv with plain DOB passwords',
)]
class ImportUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFile = 'employees.csv'; // Must be in project root folder

        if (!file_exists($csvFile)) {
            $io->error('File employees.csv not found!');
            return Command::FAILURE;
        }

        if (($handle = fopen($csvFile, "r")) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // 1. Skip Header Row (Assumes first row has "Name" or "Email" in it)
                if ($row === 0 && !is_numeric(substr($data[2], 0, 1))) { 
                    $row++;
                    continue; 
                }

                // 2. Map Columns (Adjust numbers if your excel is different)
                // [0] Name, [1] Last Name, [2] DOB (7/31/1976), [3] Email
                $firstName = trim($data[0]);
                $lastName = trim($data[1]);
                $rawDob = trim($data[2]); // "7/31/1976"
                $email = trim($data[3]);

                if (!$email) continue; // Skip empty rows

                // 3. Convert Date Format: "7/31/1976" -> "19760731"
                try {
                    $dateObj = new \DateTime($rawDob); 
                    $passwordDOB = $dateObj->format('Ymd'); // Becomes 19760731
                } catch (\Exception $e) {
                    $io->warning("Skipping $email - Bad date format: $rawDob");
                    continue;
                }

                // 4. Create User
                // Check if exists
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                }

                // Set Name (Assuming you have a setName or setFirstName)
                // $user->setName($firstName . ' ' . $lastName); 

                // 5. SET PLAIN TEXT PASSWORD (No Hashing)
                $user->setPassword($passwordDOB);
                
                $user->setRoles(['ROLE_USER']);

                $this->entityManager->persist($user);
                $io->writeln("Imported: $email | Pass: $passwordDOB");
                
                $row++;
            }
            fclose($handle);
            $this->entityManager->flush();
            
            $io->success("Imported $row users successfully!");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}