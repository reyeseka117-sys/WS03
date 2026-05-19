<!-- Nav -->
    <header class="bg-blue-900 text-white p-4">
      <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-3xl font-semibold">
          <a href="<?= appRoot() ?>/" class="text-white hover:underline">Job Seeker</a>
        </h1>
        <nav class="space-x-4">
          <a href="<?= appRoot() ?>/login" class="text-white hover:underline">Login</a>
          <a href="<?= appRoot() ?>/register" class="text-white hover:underline">Register</a>
          <a
            href="<?= appRoot() ?>/listings/create"
            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded hover:shadow-md transition duration-300"
            ><i class="fa fa-edit"></i> Post a Job</a
          >
        </nav>
      </div>
    </header>