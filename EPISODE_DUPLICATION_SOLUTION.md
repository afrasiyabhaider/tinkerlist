# Episode Duplication Solution

This repository demonstrates an optimized approach for duplicating nested structures, focusing on a real-world use case of an **Episode** with deeply nested relationships.

---

## 🧩 **Overview**

### The Episode Structure:
An **Episode** consists of the following nested relationships:

- **Episode**
  - **Parts**
    - **Items**
      - **Blocks**
        - **Block Fields**
        - **Media**

### Goal:
Efficiently duplicate an **Episode**, including all nested children, while ensuring:
- **Database efficiency**
- **Scalability**
- **Observability**
- **Resiliency and failure handling**

---

## ⚙️ **Solution Details**

### 1️⃣ **High-Level Approach**
1. **Eager Load Relationships**:
   Fetch all related data in a single query to avoid the N+1 query problem.
2. **Transactional Duplication**:
   Use database transactions for atomicity and consistency.
3. **Batched Processing**:
   Process child records in manageable chunks to handle large datasets.
4. **Asynchronous Processing**:
   Utilize Laravel queue jobs to offload and parallelize duplication.
5. **Observability**:
   Integrate logging and events to track progress and errors.
6. **Failure Handling**:
   Implement retries and rollbacks to ensure data integrity.

---

### 2️⃣ **Code Implementation**

#### **Step 1: Eager Loading Data**
Load the entire nested structure in one query for efficiency.

```php
$episode = Episode::with([
    'parts.items.blocks.blockFields',
    'parts.items.blocks.media'
])->findOrFail($episodeId);
```

---

#### **Step 2: Transactional Duplication**
Handle duplication using a database transaction and process nested relationships.

```php
DB::transaction(function () use ($episode) {
    $newEpisode = $episode->replicate();
    $newEpisode->save();

    $episode->parts->chunk(100)->each(function ($parts) use ($newEpisode) {
        foreach ($parts as $part) {
            $newPart = $part->replicate();
            $newPart->episode_id = $newEpisode->id;
            $newPart->save();

            $part->items->chunk(100)->each(function ($items) use ($newPart) {
                foreach ($items as $item) {
                    $newItem = $item->replicate();
                    $newItem->part_id = $newPart->id;
                    $newItem->save();

                    $item->blocks->chunk(100)->each(function ($blocks) use ($newItem) {
                        foreach ($blocks as $block) {
                            $newBlock = $block->replicate();
                            $newBlock->item_id = $newItem->id;
                            $newBlock->save();

                            $block->blockFields->each(function ($field) use ($newBlock) {
                                $newField = $field->replicate();
                                $newField->block_id = $newBlock->id;
                                $newField->save();
                            });

                            $block->media->each(function ($media) use ($newBlock) {
                                $newMedia = $media->replicate();
                                $newMedia->block_id = $newBlock->id;
                                $newMedia->save();
                            });
                        }
                    });
                }
            });
        }
    });
});
```

---

#### **Step 3: Asynchronous Duplication**
Split duplication into smaller, independent jobs for scalability.

###### **Job Dispatch**
```php
use App\Jobs\DuplicateEpisode;

DuplicateEpisode::dispatch($episode)->onQueue('high-priority');
```

###### **Job Example**
```php
class DuplicatePartsJob implements ShouldQueue
{
    public function handle(Episode $newEpisode, Collection $parts)
    {
        DB::transaction(function () use ($newEpisode, $parts) {
            $parts->each(function ($part) use ($newEpisode) {
                $newPart = $part->replicate();
                $newPart->episode_id = $newEpisode->id;
                $newPart->save();

                DuplicateItemsJob::dispatch($newPart, $part->items);
            });
        });
    }
}
```

---

#### **Step 4: Observability**
Track progress with logs and events.

###### **Logging**
```php
Log::info("Duplicating episode: {$episode->id} -> New Episode: {$newEpisode->id}");
```

###### **Triggering Events**
```php
event(new EpisodeDuplicated($newEpisode));
```

---

#### **Step 5: Failure Handling**
- **Retries**: Queue jobs retry on failure with exponential backoff.
- **Rollbacks**: Ensure transactions are rolled back on failure.

```php
public $tries = 3;
public $backoff = 10; // Retry after 10 seconds

DB::rollBack();
Log::error("Duplication failed for Episode ID: {$episode->id}");
```

---

### 3️⃣ **Technology Stack**

- **Framework**: Laravel
- **Queue System**: AWS SQS
- **Database**: AWS RDS
- **Monitoring**: AWS CloudWatch
- **Real-Time Updates**: Laravel Events

---

### 4️⃣ **Advantages**

- **Efficiency**: Eager loading and batched processing reduce database load.
- **Scalability**: Queue jobs handle large datasets and isolate heavy operations.
- **Observability**: Logs and events provide transparency and debugging support.
- **Resiliency**: Retry logic and rollbacks maintain data integrity.
- **Minimal Impact**: Asynchronous processing minimizes disruption to platform users.

---

## 🛠️ **How to Use**

1. Clone this repository and set up your Laravel environment.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Configure your database and queue settings in `.env`.
4. Run the migrations:
   ```bash
   php artisan migrate
   ```
5. Start the queue workers:
   ```bash
   php artisan queue:work
   ```
6. Duplicate an Episode:
   ```php
   DuplicateEpisode::dispatch($episode);
   ```

---

## 🚀 **Future Enhancements**
- **Batch Job Monitoring**: Use Laravel Horizon for queue metrics.
- **Rate Limiting**: Restrict duplication requests per user.
- **Progress Feedback**: Implement real-time progress bars using WebSockets.
