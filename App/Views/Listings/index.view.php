<?php 

    loadPartial('head');

    loadPartial('navbar');
    
    loadPartial('showcase'); 

    loadPartial('topbanner'); 
    
?>

    <!-- Job Listings -->
    <section>
      <div class="container mx-auto p-4 mt-4">
        <div class="text-center text-3xl mb-4 font-bold border border-gray-300 p-3">Recent Jobs</div>
        <?php if (empty($listings)): ?>
            <div class="text-center py-16 text-gray-600 border border-gray-200 rounded-lg bg-white">
                <h3 class="text-2xl font-semibold mb-4">No job listings available yet</h3>
                <p class="text-gray-700">There are currently no jobs in the database. You can post a new job after logging in, or refresh to load demo listings.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <?php foreach($listings as $listing): ?>

              <div class="rounded-lg shadow-md bg-white">
                <div class="p-4">
                  <h2 class="text-xl font-semibold"><?= $listing->title ?></h2>
                  <p class="text-gray-700 text-lg mt-2">
                    <?= $listing->description ?>
                  </p>
                  <ul class="my-4 bg-gray-100 p-4 rounded">
                    <li class="mb-2"><strong>Salary:</strong> <?= formatSalary($listing->salary) ?></li>
                    <li class="mb-2">
                      <strong>Location:</strong> <?= $listing->city ?>, <?= $listing->state ?>
                      <span
                        class="text-xs bg-blue-500 text-white rounded-full px-2 py-1 ml-2"
                        >Local</span
                      >
                    </li>
                    <?php if (!empty(trim($listing->tags ?? ''))): ?>
                    <li class="mb-2">
                      <strong>Tags:</strong> <?= $listing->tags ?>
                    </li>
                    <?php endif; ?>
                  </ul>
                  <a href="<?= appRoot() ?>/listings/<?= $listing->id ?>"
                    class="block w-full text-center px-5 py-2.5 shadow-sm rounded border text-base font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                  >
                    Details
                  </a>
                </div>
              </div>

              <?php endforeach; ?>
            </div>
        <?php endif; ?>

      </section>
    <?php loadPartial('bottombanner'); ?>
     <?php loadPartial('footer'); ?>  
     
 
