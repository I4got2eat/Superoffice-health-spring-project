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
    description: 'Imports users from employees.csv into the User table',
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
        
        // Ensure employees.csv is in the project root folder (same level as composer.json)
        $csvFile = 'employees.csv'; 

        if (!file_exists($csvFile)) {
            $io->error('File employees.csv not found! Please place it in the project root.');
            return Command::FAILURE;
        }

        if (($handle = fopen($csvFile, "r")) !== false) {
            $row = 0;
            $importedCount = 0;
            
            $io->title('Starting Import...');

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // CSV Structure based on your input:
                // [0] First Name (Johan)
                // [1] Last Name (Schroer)
                // [2] DOB (7/31/1976)
                // [3] Email (johan.schror@...)

                $firstName = trim($data[0] ?? '');
                $lastName  = trim($data[1] ?? '');
                $rawDob    = trim($data[2] ?? '');
                $email     = trim($data[3] ?? '');

                // 1. Validation: Skip rows without an '@' symbol in the email column
                if (!str_contains($email, '@')) {
                    continue; 
                }

                // 2. Format the Date: "7/31/1976" -> "19760731"
                try {
                    $dateObj = new \DateTime($rawDob);
                    $plainPassword = $dateObj->format('Ymd'); // Result: 19760731
                } catch (\Exception $e) {
                    $io->warning("Skipping $firstName $lastName - Invalid Date format: $rawDob");
                    continue;
                }

                // 3. Check for duplicates in the Database
                // We use 'workEmail' because your Entity has it defined as unique
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['workEmail' => $email]);
                
                if ($existingUser) {
                    $io->writeln("Skipping existing user: $email");
                    continue;
                }

                // 4. Create and Populate the User Entity
                $user = new User();

                // Merge First + Last Name into 'name'
                $fullName = $firstName . ' ' . $lastName;
                $user->setName($fullName);

                // Set Email (Your entity setter automatically lowercases it)
                $user->setWorkEmail($email);

                // Set Password (Plain text date: 19760731)
                $user->setLoginPassword($plainPassword);

                // Ensure they are not Admin
                $user->setIsAdmin(false);

                // Persist to DB queue
                $this->entityManager->persist($user);
                $importedCount++;

                $io->writeln("Imported: $fullName | Pass: $plainPassword");
            }

            fclose($handle);
            
            // Execute the SQL to save to Supabase
            $this->entityManager->flush();

            $io->success("Success! Imported $importedCount users into the database.");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}