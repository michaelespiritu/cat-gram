const { registerBlockType } = wp.blocks;
const { useState, useEffect } = wp.element;

registerBlockType('cat-gram/block', {
    title: 'Cat Gram',
    icon: 'images-alt2',
    category: 'widgets',

    attributes: {
        breed: {
            type: 'string',
            default: 'pers' // default to Persian breed
        }
    },

    edit({ attributes, setAttributes}) {
        const [breeds, setBreeds] = useState([]);

        useEffect(() => {
            // Fetch breed list from TheCatAPI on block mount
            fetch('https://api.thecatapi.com/v1/breeds')
                .then(response => response.json())
                .then(data => {
                  setBreeds(data);
              });
      }, [attributes.breed]); 
        

        return (
            <div className="cat-gram-block">
                
                <div>
                    <p>Select a Breed:</p>
                    <select 
                        value={attributes.breed} 
                        onChange={(e) => setAttributes({ breed: e.target.value })}
                    >
                        {breeds.map(breed => (
                            <option key={breed.id} value={breed.id}>
                                {breed.name}
                            </option>
                        ))}
                    </select>
                </div>
            </div>
        );
    },

    save() {
      // Save only the breed ID as an attribute (not the actual image URL)
      return null;
  }
});
