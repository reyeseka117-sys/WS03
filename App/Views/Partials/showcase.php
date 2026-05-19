<!-- Showcase -->
    <section
      class="showcase relative bg-cover bg-center bg-no-repeat h-72 flex items-center"
    >
      <div class="overlay"></div>
      <div class="container mx-auto text-center z-10">
        <h1 class="showcase-title text-white mb-2">Job Seeker</h1>
        <p class="showcase-subtitle text-white mb-6">Find Your Dream Job with confidence.</p>
        <form method="GET" action="<?= appRoot() ?>/listings/search" class="mb-4 block mx-5 md:mx-auto">
          <input
            type="text"
            name="keywords"
            placeholder="Keywords"
            class="showcase-input w-full md:w-auto mb-2 px-4 py-2 focus:outline-none"
          />
          <input
            type="text"
            name="location"
            placeholder="Location"
            class="w-full md:w-auto mb-2 px-4 py-2 focus:outline-none"
          />
          <button
            class="showcase-button w-full md:w-auto px-4 py-2 focus:outline-none"
          >
          <i class="fa fa-search"></i> Search
          </button>
        </form>
      </div>
    </section>