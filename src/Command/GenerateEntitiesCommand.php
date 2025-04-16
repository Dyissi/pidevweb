<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[AsCommand(
    name: 'app:generate:entities',
    description: 'Generates entity classes and repositories from database schema',
)]
class GenerateEntitiesCommand extends Command
{
    private Connection $connection;
    private ?AbstractSchemaManager $schemaManager = null;
    private array $generatedRelations = [];

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating Entities and Repositories');
    
        // Add this line to specify which tables to process
        $tablesToProcess = ['data', 'training_session'];
    
        try {
            // Clear any cached schema information
            $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $this->schemaManager = null;
    
            $schemaManager = $this->getSchemaManager();
            $allTables = $schemaManager->listTables();
    
            $tables = array_filter($allTables, function(Table $table) use ($tablesToProcess) {
                return in_array($table->getName(), $tablesToProcess);
            });
    
            if (empty($tables)) {
                $io->error('None of the specified tables were found in the database');
                return Command::FAILURE;
            }
    
            $oneToManyRelations = [];
            $manyToOneRelationsName = [];
            $oneToManyRelationsName = [];
    
            // First pass - generate entities and repositories
            foreach ($tables as $table) {
                $this->generateEntity($table, $oneToManyRelations, $manyToOneRelationsName, $oneToManyRelationsName);
                $io->success(sprintf('Generated: %s', $this->getEntityName($table)));
            }
    
            // Second pass - add relationships
            foreach ($tables as $table) {
                $this->generateEntity($table, $oneToManyRelations, $manyToOneRelationsName, $oneToManyRelationsName);
                $io->success(sprintf('Relations added: %s', $this->getEntityName($table)));
            }
    
            $io->success('All entities and repositories generated successfully!');
            return Command::SUCCESS;
    
        } catch (\Exception $e) {
            $io->error(sprintf('Error: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function getSchemaManager(): AbstractSchemaManager
    {
        if ($this->schemaManager === null) {
            $this->schemaManager = $this->connection->createSchemaManager();
        }
        return $this->schemaManager;
    }

    private function generateEntity(Table $table, array &$oneToManyRelations, array &$manyToOneRelationsName, array &$oneToManyRelationsName): void
    {
        $tableName = $table->getName();
        $className = $this->toPascalCase($this->removePrefix($tableName));

        // Generate repository first
        $this->generateRepository($className);

        $entityCode = "<?php\n\nnamespace App\\Entity;\n\n";
        $entityCode .= "use App\\Repository\\{$className}Repository;\n";
        $entityCode .= "use Doctrine\\ORM\\Mapping as ORM;\n";
        $entityCode .= "use Doctrine\\Common\\Collections\\Collection;\n";
        $entityCode .= "use Doctrine\\Common\\Collections\\ArrayCollection;\n\n";

        $entityCode .= "#[ORM\\Entity(repositoryClass: {$className}Repository::class)]\n";
        $entityCode .= "class $className\n{\n";

        $primaryKeys = $table->getPrimaryKey()?->getColumns() ?? [];
        $foreignKeys = $this->getForeignKeys([$tableName]);

        // Generate properties
        foreach ($table->getColumns() as $column) {
            $entityCode .= $this->generateProperty($column, $primaryKeys, $foreignKeys, $className, $oneToManyRelations, $manyToOneRelationsName, $oneToManyRelationsName);
        }

        // Generate getters and setters
        foreach ($table->getColumns() as $column) {
            $entityCode .= $this->generateGettersAndSetters($column);
        }

        // Add relationship methods
        if (isset($oneToManyRelations[$className])) {
            foreach ($oneToManyRelations[$className] as $relation) {
                $entityCode .= $relation;
            }
        }

        $entityCode .= "}\n";

        file_put_contents(sprintf('%s/../../src/Entity/%s.php', __DIR__, $className), $entityCode);
    }

    private function generateRepository(string $className): void
    {
        $repositoryPath = sprintf('%s/../../src/Repository/%sRepository.php', __DIR__, $className);
        
        if (!file_exists($repositoryPath)) {
            $repositoryCode = <<<PHP
<?php

namespace App\Repository;

use App\Entity\\$className;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class {$className}Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry \$registry)
    {
        parent::__construct(\$registry, $className::class);
    }

    // Add your custom repository methods here
}
PHP;
            file_put_contents($repositoryPath, $repositoryCode);
        }
    }

    private function generateProperty(Column $column, array $primaryKeys, array $foreignKeys, string $className, array &$oneToManyRelations, array &$manyToOneRelationsName, array &$oneToManyRelationsName): string
    {
        $columnName = $column->getName();
        $propertyName = $this->toCamelCase($columnName);
        $isPrimaryKey = in_array($columnName, $primaryKeys);
        $isForeignKey = isset($foreignKeys[$columnName]);

        $type = $column->getType();
        $doctrineType = ($type instanceof StringType && method_exists($column, 'getPlatformOptions') && isset($column->getPlatformOptions()['enum']))
            ? 'string'
            : $this->mapDoctrineType(get_class($type));

        $phpType = $this->mapPhpType($doctrineType);
        $length = $column->getLength();
        $lengthAnnotation = ($doctrineType === 'string' && $length) ? ", length: $length" : "";

        $propertyCode = "\n    " . ($isPrimaryKey ? "#[ORM\\Id]\n    #[ORM\\GeneratedValue]\n    " : "");

        if ($isForeignKey) {
            $relatedEntity = $foreignKeys[$columnName]['referencedTable'];
            $relatedClassName = $this->toPascalCase($this->removePrefix($relatedEntity));
            $relatedPropertyName = $this->toCamelCase($relatedClassName);

            $propertyCode .= "    #[ORM\\ManyToOne(targetEntity: $relatedClassName::class, inversedBy: '" . lcfirst($className) . "s')]\n";
            $propertyCode .= "    #[ORM\\JoinColumn(name: '$columnName', referencedColumnName: 'id', onDelete: 'CASCADE')]\n";
            $propertyCode .= "    private ?$relatedClassName \$$propertyName = null;\n";

            $manyToOneRelationsName[$className] = $relatedClassName;
            $oneToManyRelationsName[$relatedClassName] = $className;
            $oneToManyRelations[$relatedClassName][] = "\n    #[ORM\\OneToMany(mappedBy: \"$propertyName\", targetEntity: $className::class)]\n    private Collection \$" . lcfirst($className) . "s;\n";
        } else {
            $propertyCode .= "#[ORM\\Column(type: \"$doctrineType\"$lengthAnnotation)]\n";
            $propertyCode .= "    private ?$phpType \$$propertyName = null;\n";
        }

        return $propertyCode;
    }

    private function generateGettersAndSetters(Column $column): string
    {
        $columnName = $column->getName();
        $propertyName = $this->toCamelCase($columnName);
        $methodName = ucfirst($propertyName);
        $phpType = $this->mapPhpType($column->getType()->getName());

        return "
    public function get$methodName(): ?$phpType
    {
        return \$this->$propertyName;
    }

    public function set$methodName(?$phpType \$$propertyName): static
    {
        \$this->$propertyName = \$$propertyName;
        return \$this;
    }\n";
    }

    private function getForeignKeys(array $tables): array
    {
        $foreignKeys = [];
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($tables as $tableName) {
            $tableForeignKeys = $schemaManager->listTableForeignKeys($tableName);
            foreach ($tableForeignKeys as $foreignKey) {
                foreach ($foreignKey->getLocalColumns() as $columnName) {
                    $foreignKeys[$columnName] = [
                        'referencedTable' => $foreignKey->getForeignTableName(),
                        'referencedColumn' => $foreignKey->getForeignColumns()[0]
                    ];
                }
            }
        }

        return $foreignKeys;
    }

    private function mapDoctrineType(string $typeClass): string
    {
        return match ($typeClass) {
            'Doctrine\DBAL\Types\IntegerType' => 'integer',
            'Doctrine\DBAL\Types\BigIntType' => 'bigint',
            'Doctrine\DBAL\Types\SmallIntType' => 'smallint',
            'Doctrine\DBAL\Types\BooleanType' => 'boolean',
            'Doctrine\DBAL\Types\DateTimeType', 'Doctrine\DBAL\Types\TimestampType' => 'datetime',
            'Doctrine\DBAL\Types\DateType' => 'date',
            'Doctrine\DBAL\Types\TextType' => 'text',
            'Doctrine\DBAL\Types\DecimalType', 'Doctrine\DBAL\Types\FloatType', 'Doctrine\DBAL\Types\DoubleType' => 'float',
            default => 'string',
        };
    }

    private function mapPhpType(string $doctrineType): string
    {
        return match ($doctrineType) {
            'integer', 'bigint', 'smallint' => 'int',
            'boolean' => 'bool',
            'datetime', 'date' => '\\DateTimeInterface',
            'float', 'decimal' => 'float',
            default => 'string',
        };
    }

    private function toPascalCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    private function toCamelCase(string $name): string
    {
        return lcfirst($this->toPascalCase($name));
    }

    private function removePrefix(string $tableName): string
    {
        $prefixes = ['performance_', 'session_', 'tbl_', 'app_'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($tableName, $prefix)) {
                return substr($tableName, strlen($prefix));
            }
        }
        return $tableName;
    }

    private function getEntityName(Table $table): string
    {
        return $this->toPascalCase($this->removePrefix($table->getName()));
    }
}